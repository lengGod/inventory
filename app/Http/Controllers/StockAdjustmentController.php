<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');

        $adjustments = StockAdjustment::with('items.product')
            ->when($search, fn($q) => $q->where('doc_number', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function (StockAdjustment $adjustment) {
                $adjustment->setAttribute(
                    'type',
                    (float) $adjustment->items->sum('diff') < 0 ? 'decrease' : 'increase',
                );

                return $adjustment;
            });

        return Inertia::render('StockAdjustments/Index', [
            'adjustments' => $adjustments,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ]);

        $currentStocks = Stock::query()
            ->selectRaw('product_id, SUM(quantity) as totalQty')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        return Inertia::render('StockAdjustments/Create', [
            'products' => $products,
            'currentStocks' => $currentStocks,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:increase,decrease',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $adjustment = DB::transaction(function () use ($validated) {
                $reason = $validated['reason'];

                $adjustment = StockAdjustment::create([
                    'doc_number' => $this->generateDocNumber(),
                    'date' => now()->toDateString(),
                    'status' => 'draft',
                    'reason' => $reason,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    $stock = Stock::query()
                        ->where('product_id', $product->id)
                        ->where('quantity', '>', 0)
                        ->orderByDesc('quantity')
                        ->first();

                    $location = $stock?->location ?? Location::orderBy('id')->firstOrFail();
                    $systemQty = (float) ($stock?->quantity ?? 0);

                    $diff = $validated['type'] === 'increase'
                        ? (float) $item['quantity']
                        : -1 * (float) $item['quantity'];

                    if ($systemQty + $diff < 0) {
                        $diff = -$systemQty;
                    }

                    StockAdjustmentItem::create([
                        'stock_adjustment_id' => $adjustment->id,
                        'product_id' => $product->id,
                        'location_id' => $location->id,
                        'batch_id' => $stock?->batch_id,
                        'system_qty' => $systemQty,
                        'physical_qty' => $systemQty + $diff,
                        'diff' => $diff,
                        'reason' => $reason,
                    ]);
                }

                return $adjustment;
            });

            return redirect()
                ->route('stock-adjustments.show', $adjustment)
                ->with('success', 'Penyesuaian stok disimpan sebagai draft.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan penyesuaian stok: '.$e->getMessage());
        }
    }

    public function show(StockAdjustment $stockAdjustment): Response
    {
        $stockAdjustment->load('items.product', 'items.location', 'items.batch');

        $totalDiff = (float) $stockAdjustment->items->sum('diff');

        return Inertia::render('StockAdjustments/Show', [
            'adjustment' => [
                'id' => $stockAdjustment->id,
                'doc_number' => $stockAdjustment->doc_number,
                'date' => $stockAdjustment->date,
                'type' => $totalDiff < 0 ? 'decrease' : 'increase',
                'reason' => $stockAdjustment->reason,
                'notes' => null,
                'status' => $stockAdjustment->status,
                'items' => $stockAdjustment->items->map(fn (StockAdjustmentItem $item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'location' => $item->location?->name,
                    'batch_number' => $item->batch?->batch_number ?? 'N/A',
                    'system_qty' => (float) $item->system_qty,
                    'physical_qty' => (float) $item->physical_qty,
                    'diff' => (float) $item->diff,
                    'quantity' => abs((float) $item->diff),
                ]),
                'created_at' => $stockAdjustment->created_at,
                'posted_at' => $stockAdjustment->approved_at,
            ],
        ]);
    }

    public function post(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return back()->with('error', 'Hanya dokumen draft yang dapat diposting.');
        }

        try {
            DB::transaction(function () use ($stockAdjustment) {
                $userId = auth()->id();

                foreach ($stockAdjustment->items()->with(['product', 'location', 'batch'])->get() as $item) {
                    $diff = (float) $item->diff;

                    if ($diff === 0.0) {
                        continue;
                    }

                    $movement = $this->stockService->adjust(
                        product: $item->product,
                        location: $item->location,
                        diff: $diff,
                        batch: $item->batch,
                        reason: $stockAdjustment->reason ?? '',
                        userId: $userId,
                    );

                    $movement->forceFill(['reference_id' => $stockAdjustment->id])->save();
                }

                $stockAdjustment->update([
                    'status' => 'posted',
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ]);
            });

            return redirect()
                ->route('stock-adjustments.show', $stockAdjustment)
                ->with('success', 'Penyesuaian stok berhasil diposting.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal posting penyesuaian stok: '.$e->getMessage());
        }
    }

    public function destroy(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status === 'posted') {
            return back()->with('error', 'Dokumen yang sudah diposting tidak dapat dihapus.');
        }

        $stockAdjustment->items()->delete();
        $stockAdjustment->delete();

        return redirect()
            ->route('stock-adjustments.index')
            ->with('success', 'Draft penyesuaian stok berhasil dihapus.');
    }

    private function generateDocNumber(): string
    {
        $prefix = 'ADJ-'.now()->format('Ym');
        $latest = StockAdjustment::where('doc_number', 'like', $prefix.'%')
            ->orderByDesc('doc_number')
            ->value('doc_number');

        $nextSeq = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return sprintf('%s-%04d', $prefix, $nextSeq);
    }
}
