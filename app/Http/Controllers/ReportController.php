<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Halaman pusat laporan.
     */
    public function index(): Response
    {
        $today = Carbon::today();

        $stockRow = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->selectRaw('COALESCE(SUM(stocks.quantity), 0) as total_qty, COALESCE(SUM(stocks.quantity * COALESCE(products.purchase_price, 0)), 0) as total_value')
            ->first();

        $lowStockCount = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->selectRaw('stocks.product_id, SUM(stocks.quantity) as total_qty, products.min_stock')
            ->groupBy('stocks.product_id', 'products.min_stock')
            ->havingRaw('total_qty < products.min_stock')
            ->havingRaw('total_qty > 0')
            ->count();

        $expiredCount = Batch::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->count();

        $nearExpiryCount = Batch::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $today->copy()->addDays(30))
            ->count();

        $movementCount = StockMovement::whereDate('created_at', '>=', $today->copy()->subDays(30))->count();

        return Inertia::render('Reports/Index', [
            'summary' => [
                'total_sku' => Product::where('is_active', true)->count(),
                'total_qty' => (float) ($stockRow->total_qty ?? 0),
                'total_value' => (float) ($stockRow->total_value ?? 0),
                'low_stock' => $lowStockCount,
                'expired' => $expiredCount,
                'near_expiry' => $nearExpiryCount,
                'movements_30d' => $movementCount,
            ],
        ]);
    }

    /**
     * Laporan stok seluruh barang.
     */
    public function stock(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with(['category:id,name', 'unit:id,name,short_name'])
            ->withSum('stocks as total_qty', 'quantity')
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category?->name,
                'unit' => $product->unit?->short_name,
                'total_qty' => (float) ($product->total_qty ?? 0),
                'min_stock' => (float) $product->min_stock,
                'purchase_price' => (float) ($product->purchase_price ?? 0),
                'value' => (float) ($product->total_qty ?? 0) * (float) ($product->purchase_price ?? 0),
            ]);

        $stockRow = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->selectRaw('COALESCE(SUM(stocks.quantity), 0) as total_qty, COALESCE(SUM(stocks.quantity * COALESCE(products.purchase_price, 0)), 0) as total_value')
            ->first();

        return Inertia::render('Reports/Stock', [
            'products' => $products,
            'filters' => ['search' => $search],
            'summary' => [
                'total_sku' => Product::where('is_active', true)->count(),
                'total_qty' => (float) ($stockRow->total_qty ?? 0),
                'total_value' => (float) ($stockRow->total_value ?? 0),
            ],
        ]);
    }

    /**
     * Laporan stok menipis (di bawah stok minimum).
     */
    public function lowStock(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $rows = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('units', 'products.unit_id', '=', 'units.id')
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%");
                });
            })
            ->selectRaw('products.id, products.sku, products.name, categories.name as category, units.short_name as unit, products.min_stock, COALESCE(SUM(stocks.quantity), 0) as total_qty')
            ->groupBy('products.id', 'products.sku', 'products.name', 'categories.name', 'units.short_name', 'products.min_stock')
            ->havingRaw('total_qty < products.min_stock')
            ->havingRaw('total_qty > 0')
            ->orderByRaw('(products.min_stock - total_qty) DESC')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($row) => [
                'id' => $row->id,
                'sku' => $row->sku,
                'name' => $row->name,
                'category' => $row->category,
                'unit' => $row->unit,
                'total_qty' => (float) $row->total_qty,
                'min_stock' => (float) $row->min_stock,
                'shortage' => (float) $row->min_stock - (float) $row->total_qty,
            ]);

        $aggregate = DB::table('products')
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->selectRaw('products.id, products.min_stock, COALESCE(SUM(stocks.quantity), 0) as total_qty')
            ->groupBy('products.id', 'products.min_stock')
            ->havingRaw('total_qty < products.min_stock')
            ->havingRaw('total_qty > 0')
            ->get();

        return Inertia::render('Reports/LowStock', [
            'products' => $rows,
            'filters' => ['search' => $search],
            'summary' => [
                'count' => $aggregate->count(),
                'total_shortage' => (float) $aggregate->sum(fn ($row) => max(0, (float) $row->min_stock - (float) $row->total_qty)),
            ],
        ]);
    }

    /**
     * Laporan batch kedaluwarsa / akan kedaluwarsa.
     */
    public function expiry(Request $request): Response
    {
        $today = Carbon::today();
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $batches = Batch::query()
            ->with([
                'product:id,name,sku,unit_id',
                'product.unit:id,short_name',
                'supplier:id,name',
            ])
            ->withSum('stocks as total_qty', 'quantity')
            ->whereNotNull('expiry_date')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status === 'expired', fn ($query) => $query->whereDate('expiry_date', '<', $today))
            ->when($status === 'near_expiry', fn ($query) => $query->whereDate('expiry_date', '>=', $today)->whereDate('expiry_date', '<=', $today->copy()->addDays(30)))
            ->when($status === 'active', fn ($query) => $query->whereDate('expiry_date', '>', $today->copy()->addDays(30)))
            ->orderBy('expiry_date')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Batch $batch) use ($today) {
                $daysLeft = (int) $today->diffInDays($batch->expiry_date->copy()->startOfDay(), false);
                $totalQty = (float) ($batch->total_qty ?? 0);

                $batchStatus = match (true) {
                    $daysLeft < 0 => 'expired',
                    $totalQty <= 0 => 'depleted',
                    $daysLeft <= 30 => 'near_expiry',
                    default => 'active',
                };

                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'product' => $batch->product?->name,
                    'sku' => $batch->product?->sku,
                    'unit' => $batch->product?->unit?->short_name,
                    'supplier' => $batch->supplier?->name,
                    'manufactured_date' => $batch->manufactured_date?->format('Y-m-d'),
                    'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                    'days_left' => $daysLeft,
                    'total_qty' => $totalQty,
                    'status' => $batchStatus,
                ];
            });

        return Inertia::render('Reports/Expiry', [
            'batches' => $batches,
            'filters' => ['status' => $status, 'search' => $search],
            'summary' => [
                'expired' => Batch::whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today)->count(),
                'near_expiry' => Batch::whereNotNull('expiry_date')->whereDate('expiry_date', '>=', $today)->whereDate('expiry_date', '<=', $today->copy()->addDays(30))->count(),
                'active' => Batch::whereNotNull('expiry_date')->whereDate('expiry_date', '>', $today->copy()->addDays(30))->count(),
            ],
        ]);
    }

    /**
     * Laporan mutasi stok (kartu stok).
     */
    public function movements(Request $request): Response
    {
        $base = $this->movementQuery($request);

        $totalIn = (float) (clone $base)->where('qty', '>', 0)->sum('qty');
        $totalOut = (float) (clone $base)->where('qty', '<', 0)->sum('qty');
        $totalAdjust = (float) (clone $base)->where('type', 'adjust')->sum('qty');
        $totalRows = (clone $base)->count();

        $movements = $base
            ->with([
                'product:id,name,sku',
                'location:id,name',
                'batch:id,batch_number',
                'creator:id,name',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'date' => $movement->created_at?->format('d/m/Y H:i'),
                'product' => $movement->product?->name,
                'sku' => $movement->product?->sku,
                'type' => $movement->type,
                'qty' => (float) $movement->qty,
                'balance_after' => (float) $movement->balance_after,
                'location' => $movement->location?->name,
                'batch' => $movement->batch?->batch_number,
                'creator' => $movement->creator?->name,
            ]);

        return Inertia::render('Reports/Movements', [
            'movements' => $movements,
            'filters' => [
                'date_from' => $request->query('date_from', ''),
                'date_to' => $request->query('date_to', ''),
                'type' => $request->query('type', ''),
                'search' => $request->query('search', ''),
            ],
            'summary' => [
                'total_rows' => $totalRows,
                'total_in' => $totalIn,
                'total_out' => abs($totalOut),
                'total_adjust' => $totalAdjust,
            ],
        ]);
    }

    /**
     * Ekspor laporan stok ke CSV.
     */
    public function exportStock(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('search', ''));

        $rows = Product::query()
            ->with(['category:id,name', 'unit:id,short_name'])
            ->withSum('stocks as total_qty', 'quantity')
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                $product->sku,
                $product->name,
                $product->category?->name,
                $product->unit?->short_name,
                (float) ($product->total_qty ?? 0),
                (float) $product->min_stock,
                (float) ($product->purchase_price ?? 0),
                (float) ($product->total_qty ?? 0) * (float) ($product->purchase_price ?? 0),
            ]);

        return $this->streamCsv('laporan-stok-' . now()->format('Y-m-d') . '.csv', [
            'SKU', 'Nama Barang', 'Kategori', 'Satuan', 'Stok', 'Stok Minimum', 'Harga Beli', 'Nilai Persediaan',
        ], $rows);
    }

    /**
     * Ekspor laporan stok menipis ke CSV.
     */
    public function exportLowStock(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('search', ''));

        $rows = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('units', 'products.unit_id', '=', 'units.id')
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%");
                });
            })
            ->selectRaw('products.sku, products.name, categories.name as category, units.short_name as unit, products.min_stock, COALESCE(SUM(stocks.quantity), 0) as total_qty')
            ->groupBy('products.id', 'products.sku', 'products.name', 'categories.name', 'units.short_name', 'products.min_stock')
            ->havingRaw('total_qty < products.min_stock')
            ->havingRaw('total_qty > 0')
            ->orderByRaw('(products.min_stock - total_qty) DESC')
            ->get()
            ->map(fn ($row) => [
                $row->sku,
                $row->name,
                $row->category,
                $row->unit,
                (float) $row->total_qty,
                (float) $row->min_stock,
                (float) $row->min_stock - (float) $row->total_qty,
            ]);

        return $this->streamCsv('laporan-stok-menipis-' . now()->format('Y-m-d') . '.csv', [
            'SKU', 'Nama Barang', 'Kategori', 'Satuan', 'Stok', 'Stok Minimum', 'Kekurangan',
        ], $rows);
    }

    /**
     * Ekspor laporan batch kedaluwarsa ke CSV.
     */
    public function exportExpiry(Request $request): StreamedResponse
    {
        $today = Carbon::today();
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $labels = [
            'expired' => 'Kedaluwarsa',
            'near_expiry' => 'Segera Kedaluwarsa',
            'active' => 'Aktif',
            'depleted' => 'Habis',
        ];

        $rows = Batch::query()
            ->with(['product:id,name,sku,unit_id', 'product.unit:id,short_name', 'supplier:id,name'])
            ->withSum('stocks as total_qty', 'quantity')
            ->whereNotNull('expiry_date')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status === 'expired', fn ($query) => $query->whereDate('expiry_date', '<', $today))
            ->when($status === 'near_expiry', fn ($query) => $query->whereDate('expiry_date', '>=', $today)->whereDate('expiry_date', '<=', $today->copy()->addDays(30)))
            ->when($status === 'active', fn ($query) => $query->whereDate('expiry_date', '>', $today->copy()->addDays(30)))
            ->orderBy('expiry_date')
            ->get()
            ->map(function (Batch $batch) use ($today, $labels) {
                $daysLeft = (int) $today->diffInDays($batch->expiry_date->copy()->startOfDay(), false);
                $totalQty = (float) ($batch->total_qty ?? 0);

                $batchStatus = match (true) {
                    $daysLeft < 0 => 'expired',
                    $totalQty <= 0 => 'depleted',
                    $daysLeft <= 30 => 'near_expiry',
                    default => 'active',
                };

                return [
                    $batch->batch_number,
                    $batch->product?->name,
                    $batch->product?->sku,
                    $batch->product?->unit?->short_name,
                    $batch->supplier?->name,
                    $batch->manufactured_date?->format('Y-m-d'),
                    $batch->expiry_date?->format('Y-m-d'),
                    $daysLeft,
                    $totalQty,
                    $labels[$batchStatus] ?? $batchStatus,
                ];
            });

        return $this->streamCsv('laporan-kedaluwarsa-' . now()->format('Y-m-d') . '.csv', [
            'Nomor Batch', 'Nama Barang', 'SKU', 'Satuan', 'Supplier', 'Tanggal Produksi', 'Tanggal Kedaluwarsa', 'Sisa Hari', 'Sisa Stok', 'Status',
        ], $rows);
    }

    /**
     * Ekspor laporan mutasi stok ke CSV.
     */
    public function exportMovements(Request $request): StreamedResponse
    {
        $labels = [
            'in' => 'Masuk',
            'out' => 'Keluar',
            'adjust' => 'Penyesuaian',
            'void' => 'Void',
        ];

        $rows = $this->movementQuery($request)
            ->with(['product:id,name,sku', 'location:id,name', 'batch:id,batch_number', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (StockMovement $movement) => [
                $movement->created_at?->format('d/m/Y H:i'),
                $movement->product?->name,
                $movement->product?->sku,
                $labels[$movement->type] ?? $movement->type,
                (float) $movement->qty,
                (float) $movement->balance_after,
                $movement->location?->name,
                $movement->batch?->batch_number,
                $movement->creator?->name,
            ]);

        return $this->streamCsv('laporan-mutasi-stok-' . now()->format('Y-m-d') . '.csv', [
            'Tanggal', 'Nama Barang', 'SKU', 'Tipe', 'Qty', 'Saldo Setelah', 'Lokasi', 'Batch', 'Dibuat Oleh',
        ], $rows);
    }

    private function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function movementQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        return StockMovement::query()
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->query('type')))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('product', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    })->orWhereHas('batch', function ($query) use ($search) {
                        $query->where('batch_number', 'like', "%{$search}%");
                    });
                });
            });
    }
}
