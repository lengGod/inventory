<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Stock;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StockAdjustmentController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(): Response
    {
        $adjustments = StockAdjustment::with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('StockAdjustments/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    public function create(): Response
    {
        $products = Product::orderBy('name')->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
            ]);

        $currentStocks = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('stocks.product_id, SUM(stocks.quantity) as totalQty')
            ->groupBy('stocks.product_id')
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
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $docNumber = $this->generateDocNumber();

            $adjustment = StockAdjustment::create([
                'doc_number' => $docNumber,
                'type' => $validated['type'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'posted_at' => null,
            ]);

            foreach ($validated['items'] as $item) {
                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('stock-adjustments.show', $adjustment)->with('success', 'Penyesuaian stok draft berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error: ' . $e->getMessage());
        }
    }

    public function show(StockAdjustment $stockAdjustment): Response
    {
        $stockAdjustment->load('items.product');

        return Inertia::render('StockAdjustments/Show', [
            'adjustment' => [
                'id' => $stockAdjustment->id,
                'doc_number' => $stockAdjustment->doc_number,
                'type' => $stockAdjustment->type,
                'reason' => $stockAdjustment->reason,
                'notes' => $stockAdjustment->notes,
                'status' => $stockAdjustment->status,
                'items' => $stockAdjustment->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                ]),
                'created_at' => $stockAdjustment->created_at,
                'posted_at' => $stockAdjustment->posted_at,
            ],
        ]);
    }

    public function post(StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return back()->withErrors('Hanya draft dapat di-post');
        }

        DB::beginTransaction();
        try {
            $typeMap = [
                'increase' => 'in',
                'decrease' => 'out',
            ];
            $movementType = $typeMap[$stockAdjustment->type];

            foreach ($stockAdjustment->items as $item) {
                if ($stockAdjustment->type === 'increase') {
                    $this->stockService->increase(
                        $item->product_id,
                        $item->quantity,
                        null,
                        "Penyesuaian Stok {$stockAdjustment->doc_number}: {$stockAdjustment->reason}",
                        $movementType
                    );
                } else {
                    $this->stockService->decrease(
                        $item->product_id,
                        $item->quantity,
                        null,
                        "Penyesuaian Stok {$stockAdjustment->doc_number}: {$stockAdjustment->reason}",
                        $movementType
                    );
                }
            }

            $stockAdjustment->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('stock-adjustments.show', $stockAdjustment)->with('success', 'Penyesuaian stok berhasil di-post');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error posting: ' . $e->getMessage());
        }
    }

    private function generateDocNumber(): string
    {
        $yearMonth = now()->format('Ym');
        $lastDoc = StockAdjustment::whereRaw("YEAR(created_at) = ? AND MONTH(created_at) = ?", [now()->year, now()->month])
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = ($lastDoc ? (int)substr($lastDoc->doc_number, -4) : 0) + 1;
        return "ADJ-{$yearMonth}-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
