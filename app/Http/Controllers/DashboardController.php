<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Stock;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalSKU = Stock::where('quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');

        $nilaiStok = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(stocks.quantity * products.purchase_price) as total')
            ->value('total') ?? 0;

        $stokMenipis = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('stocks.product_id, SUM(stocks.quantity) as totalQty, products.min_stock')
            ->groupBy('stocks.product_id', 'products.min_stock')
            ->havingRaw('totalQty < products.min_stock')
            ->havingRaw('totalQty > 0')
            ->count();

        $akanKedaluwarsa = Batch::where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>', Carbon::now())
            ->where('status', '!=', 'expired')
            ->count();

        $expiringBatches = Batch::with('product')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>', Carbon::now())
            ->orderBy('expiry_date')
            ->limit(5)
            ->get()
            ->map(fn ($batch) => [
                'product' => $batch->product->name,
                'batch' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->toDateString(),
                'daysLeft' => Carbon::now()->diffInDays($batch->expiry_date),
            ]);

        $lowStockItems = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('products.name, products.min_stock, SUM(stocks.quantity) as totalQty')
            ->groupBy('stocks.product_id', 'products.id', 'products.name', 'products.min_stock')
            ->havingRaw('SUM(stocks.quantity) < products.min_stock')
            ->havingRaw('SUM(stocks.quantity) > 0')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'current' => (int) $item->totalQty,
                'minimum' => $item->min_stock,
                'shortage' => $item->min_stock - (int) $item->totalQty,
            ]);

        $recentMovements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($movement) => [
                'product' => $movement->product?->name ?? '-',
                'type' => $movement->type,
                'quantity' => (float) $movement->qty,
                'date' => $movement->created_at,
            ]);

        // Grafik Masuk vs Keluar: agregasi mutasi 30 hari terakhir.
        $chartStart = Carbon::today()->subDays(29)->startOfDay();
        $chartEnd = Carbon::today()->endOfDay();

        $chartRows = StockMovement::query()
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(CASE WHEN qty > 0 THEN qty ELSE 0 END) as in_qty')
            ->selectRaw('SUM(CASE WHEN qty < 0 THEN -qty ELSE 0 END) as out_qty')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $chartLabels = [];
        $chartIn = [];
        $chartOut = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i)->toDateString();
            $row = $chartRows->get($day);

            $chartLabels[] = $day;
            $chartIn[] = (float) ($row->in_qty ?? 0);
            $chartOut[] = (float) ($row->out_qty ?? 0);
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                ['label' => 'Total SKU Aktif', 'value' => $totalSKU, 'icon' => 'package', 'color' => 'lime'],
                ['label' => 'Nilai Stok', 'value' => 'Rp '.number_format((int) $nilaiStok, 0, ',', '.'), 'icon' => 'wallet', 'color' => 'violet-text'],
                ['label' => 'Stok Menipis', 'value' => $stokMenipis, 'icon' => 'alert', 'color' => 'yellow-400'],
                ['label' => 'Akan Kedaluwarsa', 'value' => $akanKedaluwarsa, 'icon' => 'clock', 'color' => 'red-400'],
            ],
            'expiringBatches' => $expiringBatches,
            'movementChart' => [
                'labels' => $chartLabels,
                'in' => $chartIn,
                'out' => $chartOut,
            ],
            'lowStockItems' => $lowStockItems,
            'recentMovements' => $recentMovements,
        ]);
    }
}
