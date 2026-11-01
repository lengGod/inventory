<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\Product;
use App\Services\StockService;
use App\Services\BatchAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StockOutController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected BatchAllocationService $allocationService
    ) {}

    public function index(): Response
    {
        $stockOuts = StockOut::with('items.product', 'items.batch')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('StockOuts/Index', [
            'stockOuts' => $stockOuts,
        ]);
    }

    public function create(): Response
    {
        $products = Product::orderBy('name')->get();

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
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $docNumber = $this->generateDocNumber();
            
            $stockOut = StockOut::create([
                'doc_number' => $docNumber,
                'status' => 'draft',
                'notes' => $validated['notes'],
                'posted_at' => null,
            ]);

            foreach ($validated['items'] as $item) {
                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('stock-outs.show', $stockOut)->with('success', 'Barang Keluar draft berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error: ' . $e->getMessage());
        }
    }

    public function show(StockOut $stockOut): Response
    {
        $stockOut->load('items.product', 'items.batch');

        return Inertia::render('StockOuts/Show', [
            'stockOut' => [
                'id' => $stockOut->id,
                'doc_number' => $stockOut->doc_number,
                'status' => $stockOut->status,
                'notes' => $stockOut->notes,
                'items' => $stockOut->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'batch_number' => $item->batch?->batch_number ?? 'N/A',
                    'quantity' => $item->quantity,
                ]),
                'created_at' => $stockOut->created_at,
                'posted_at' => $stockOut->posted_at,
            ],
        ]);
    }

    public function post(StockOut $stockOut): RedirectResponse
    {
        if ($stockOut->status !== 'draft') {
            return back()->withErrors('Hanya draft dapat di-post');
        }

        DB::beginTransaction();
        try {
            foreach ($stockOut->items as $item) {
                $allocations = $this->allocationService->allocate(
                    $item->product_id,
                    $item->quantity
                );

                foreach ($allocations as $allocation) {
                    $this->stockService->decrease(
                        $allocation['product_id'],
                        $allocation['quantity'],
                        $allocation['batch_id'],
                        "Barang Keluar {$stockOut->doc_number}",
                        'out'
                    );

                    $item->update(['batch_id' => $allocation['batch_id']]);
                }
            }

            $stockOut->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('stock-outs.show', $stockOut)->with('success', 'Barang Keluar berhasil di-post');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error posting: ' . $e->getMessage());
        }
    }

    private function generateDocNumber(): string
    {
        $yearMonth = now()->format('Ym');
        $lastDoc = StockOut::whereRaw("YEAR(created_at) = ? AND MONTH(created_at) = ?", [now()->year, now()->month])
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = ($lastDoc ? (int)substr($lastDoc->doc_number, -4) : 0) + 1;
        return "OUT-{$yearMonth}-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
