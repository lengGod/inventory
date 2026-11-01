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
                'current' => (int)$item->totalQty,
                'minimum' => $item->min_stock,
                'shortage' => $item->min_stock - (int)$item->totalQty,
            ]);

        $recentMovements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($movement) => [
                'product' => $movement->product->name,
                'type' => $movement->movement_type,
                'quantity' => $movement->quantity,
                'date' => $movement->created_at,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                ['label' => 'Total SKU Aktif', 'value' => $totalSKU, 'icon' => 'package', 'color' => 'lime'],
                ['label' => 'Nilai Stok', 'value' => 'Rp ' . number_format((int)$nilaiStok / 1000000, 1) . 'M', 'icon' => 'wallet', 'color' => 'violet-text'],
                ['label' => 'Stok Menipis', 'value' => $stokMenipis, 'icon' => 'alert', 'color' => 'yellow-400'],
                ['label' => 'Akan Kedaluwarsa', 'value' => $akanKedaluwarsa, 'icon' => 'clock', 'color' => 'red-400'],
            ],
            'expiringBatches' => $expiringBatches,
            'lowStockItems' => $lowStockItems,
            'recentMovements' => $recentMovements,
        ]);
    }
}
