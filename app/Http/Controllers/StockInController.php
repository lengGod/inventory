<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class StockInController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        return Inertia::render('StockIns/Index', [
            'stockIns' => StockIn::with(['supplier', 'creator'])
                ->orderBy('doc_number', 'desc')
                ->paginate(50),
        ]);
    }

    public function create()
    {
        $docNumber = $this->generateDocNumber();

        return Inertia::render('StockIns/Create', [
            'docNumber' => $docNumber,
            'suppliers' => Supplier::orderBy('name')->get(),
            'locations' => Location::orderBy('code')->get(),
            'products' => Product::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'track_batch', 'track_expiry', 'unit_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doc_number' => 'required|string|unique:stock_ins',
            'date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.location_id' => 'required|exists:locations,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $stockIn = DB::transaction(function () use ($validated) {
            $stockIn = StockIn::create([
                'doc_number' => $validated['doc_number'],
                'date' => $validated['date'],
                'supplier_id' => $validated['supplier_id'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item['product_id'],
                    'location_id' => $item['location_id'],
                    'batch_id' => $item['batch_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $stockIn;
        });

        return redirect()->route('stock-ins.show', $stockIn->id)
            ->with('message', 'Barang masuk disimpan sebagai draft');
    }

    public function show(StockIn $stockIn)
    {
        return Inertia::render('StockIns/Show', [
            'stockIn' => $stockIn->load(['supplier', 'items.product', 'items.location', 'items.batch', 'creator']),
        ]);
    }

    public function post(Request $request, StockIn $stockIn)
    {
        if ($stockIn->status !== 'draft') {
            return back()->with('error', 'Hanya dokumen draft yang bisa diposting');
        }

        DB::transaction(function () use ($stockIn) {
            foreach ($stockIn->items as $item) {
                $product = $item->product;
                $location = $item->location;
                $batch = $item->batch;

                // Jika track_batch dan belum ada batch, buat batch baru
                if ($product->track_batch && !$batch) {
                    $batch = Batch::create([
                        'product_id' => $product->id,
                        'batch_number' => $this->generateBatchNumber($product),
                        'supplier_id' => $stockIn->supplier_id,
                        'status' => 'active',
                    ]);
                }

                // Increment stok
                $this->stockService->increase(
                    product: $product,
                    location: $location,
                    qty: $item->qty,
                    batch: $batch,
                    referenceType: 'StockIn',
                    referenceId: $stockIn->id,
                    userId: auth()->id(),
                );
            }

            $stockIn->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);
        });

        return redirect()->route('stock-ins.show', $stockIn->id)
            ->with('message', 'Barang masuk berhasil diposting');
    }

    public function destroy(StockIn $stockIn)
    {
        if ($stockIn->status === 'posted') {
            return back()->with('error', 'Tidak bisa menghapus dokumen yang sudah diposting. Gunakan Void jika perlu membatalkan.');
        }

        $stockIn->delete();

        return redirect()->route('stock-ins.index')
            ->with('message', 'Barang masuk dihapus');
    }

    private function generateDocNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "IN-{$year}{$month}";

        $latest = StockIn::where('doc_number', 'like', "{$prefix}%")
            ->orderBy('doc_number', 'desc')
            ->first();

        $num = 1;
        if ($latest) {
            $lastNum = (int) substr($latest->doc_number, -4);
            $num = $lastNum + 1;
        }

        return sprintf("%s-%04d", $prefix, $num);
    }

    private function generateBatchNumber(Product $product): string
    {
        $date = date('ymd');
        $latestNum = Batch::where('product_id', $product->id)
            ->where('batch_number', 'like', "B-{$date}-%")
            ->orderBy('batch_number', 'desc')
            ->value('batch_number');

        $num = 1;
        if ($latestNum) {
            $lastNum = (int) substr($latestNum, -2);
            $num = $lastNum + 1;
        }

        return sprintf("B-%s-%02d", $date, $num);
    }
}
