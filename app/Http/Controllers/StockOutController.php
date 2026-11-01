<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockOutController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');

        $stockOuts = StockOut::with('items.product', 'items.batch')
            ->when($search, fn($q) => $q->where('doc_number', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('StockOuts/Index', [
            'stockOuts' => $stockOuts,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->withSum('stocks as total_qty', 'quantity')
            ->orderBy('name')
            ->get();

        return Inertia::render('StockOuts/Create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $stockOut = DB::transaction(function () use ($validated) {
                $stockOut = StockOut::create([
                    'doc_number' => $this->generateDocNumber(),
                    'date' => now()->toDateString(),
                    'reason' => 'usage',
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $stock = Stock::query()
                        ->where('product_id', $item['product_id'])
                        ->where('quantity', '>', 0)
                        ->orderByDesc('quantity')
                        ->first();

                    StockOutItem::create([
                        'stock_out_id' => $stockOut->id,
                        'product_id' => $item['product_id'],
                        'location_id' => $stock?->location_id ?? Location::orderBy('id')->value('id'),
                        'batch_id' => null,
                        'qty' => $item['quantity'],
                    ]);
                }

                return $stockOut;
            });

            return redirect()
                ->route('stock-outs.show', $stockOut)
                ->with('success', 'Barang keluar disimpan sebagai draft.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan barang keluar: '.$e->getMessage());
        }
    }

    public function show(StockOut $stockOut): Response
    {
        $stockOut->load('items.product', 'items.batch', 'items.location');

        return Inertia::render('StockOuts/Show', [
            'stockOut' => [
                'id' => $stockOut->id,
                'doc_number' => $stockOut->doc_number,
                'date' => $stockOut->date,
                'destination' => $stockOut->destination,
                'reason' => $stockOut->reason,
                'status' => $stockOut->status,
                'notes' => $stockOut->notes,
                'items' => $stockOut->items->map(fn (StockOutItem $item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'location' => $item->location?->name,
                    'batch_number' => $item->batch?->batch_number ?? 'N/A',
                    'quantity' => (float) $item->qty,
                ]),
                'created_at' => $stockOut->created_at,
                'posted_at' => $stockOut->posted_at,
            ],
        ]);
    }

    public function post(StockOut $stockOut): RedirectResponse
    {
        if ($stockOut->status !== 'draft') {
            return back()->with('error', 'Hanya dokumen draft yang dapat diposting.');
        }

        try {
            DB::transaction(function () use ($stockOut) {
                $userId = auth()->id();

                foreach ($stockOut->items()->get() as $item) {
                    $product = $item->product;
                    $remaining = (float) $item->qty;
                    $stocks = $this->availableStocks($product);

                    if ($stocks->sum(fn (Stock $stock) => (float) $stock->quantity) < $remaining) {
                        throw new \RuntimeException("Stok tidak cukup untuk {$product->name}.");
                    }

                    $item->delete();

                    foreach ($stocks as $stock) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $take = min($remaining, (float) $stock->quantity);
                        $batch = $stock->batch_id ? Batch::find($stock->batch_id) : null;

                        $this->stockService->decrease(
                            product: $product,
                            location: $stock->location,
                            qty: $take,
                            batch: $batch,
                            referenceType: 'StockOut',
                            referenceId: $stockOut->id,
                            userId: $userId,
                        );

                        StockOutItem::create([
                            'stock_out_id' => $stockOut->id,
                            'product_id' => $product->id,
                            'location_id' => $stock->location_id,
                            'batch_id' => $stock->batch_id,
                            'qty' => $take,
                        ]);

                        $remaining -= $take;
                    }
                }

                $stockOut->update([
                    'status' => 'posted',
                    'posted_at' => now(),
                ]);
            });

            return redirect()
                ->route('stock-outs.show', $stockOut)
                ->with('success', 'Barang keluar berhasil diposting.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal posting barang keluar: '.$e->getMessage());
        }
    }

    public function destroy(StockOut $stockOut): RedirectResponse
    {
        if ($stockOut->status === 'posted') {
            return back()->with('error', 'Dokumen yang sudah diposting tidak dapat dihapus.');
        }

        $stockOut->items()->delete();
        $stockOut->delete();

        return redirect()
            ->route('stock-outs.index')
            ->with('success', 'Draft barang keluar berhasil dihapus.');
    }

    private function availableStocks(Product $product)
    {
        $today = Carbon::today();

        return Stock::query()
            ->with(['batch', 'location'])
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->where(function ($query) use ($today) {
                $query->whereNull('batch_id')
                    ->orWhereHas('batch', function ($batch) use ($today) {
                        $batch->whereNull('expiry_date')
                            ->orWhereDate('expiry_date', '>=', $today);
                    });
            })
            ->get()
            ->sortBy(fn (Stock $stock) => $stock->batch?->expiry_date?->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    private function generateDocNumber(): string
    {
        $prefix = 'OUT-'.now()->format('Ym');
        $latest = StockOut::where('doc_number', 'like', $prefix.'%')
            ->orderByDesc('doc_number')
            ->value('doc_number');

        $nextSeq = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return sprintf('%s-%04d', $prefix, $nextSeq);
    }
}
