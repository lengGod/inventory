<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * DashboardChartSeeder
 *
 * Memastikan grafik "Masuk vs Keluar (30 hari)" di Dashboard selalu punya data.
 * Seeder ini hanya berjalan jika BELUM ada mutasi stok dalam 30 hari terakhir,
 * sehingga aman dijalankan berulang (idempotent) dan tidak menduplikasi data
 * yang sudah dibuat oleh seeder transaksi lain.
 */
class DashboardChartSeeder extends Seeder
{
    public function run(): void
    {
        $start = Carbon::today()->subDays(29)->startOfDay();
        $end = Carbon::today()->endOfDay();

        $hasRecentMovement = StockMovement::whereBetween('created_at', [$start, $end])->exists();

        if ($hasRecentMovement) {
            return;
        }

        $admin = User::where('email', 'admin@gudangku.test')->first();
        $stockService = app(StockService::class);

        // Kumpulkan stok yang cukup besar supaya barang keluar tidak gagal.
        $stockPool = Stock::with(['product', 'location', 'batch'])
            ->where('quantity', '>', 60)
            ->orderBy('product_id')
            ->orderBy('location_id')
            ->limit(6)
            ->get()
            ->values();

        if ($stockPool->isEmpty() || $admin === null) {
            return;
        }

        // Pola masuk/keluar selang-seling selama 30 hari terakhir
        // supaya grafik dashboard terlihat hidup.
        $plan = [
            ['days_ago' => 28, 'type' => 'in', 'qty' => 45],
            ['days_ago' => 26, 'type' => 'out', 'qty' => 22],
            ['days_ago' => 23, 'type' => 'in', 'qty' => 60],
            ['days_ago' => 20, 'type' => 'out', 'qty' => 30],
            ['days_ago' => 17, 'type' => 'in', 'qty' => 50],
            ['days_ago' => 14, 'type' => 'out', 'qty' => 28],
            ['days_ago' => 11, 'type' => 'in', 'qty' => 65],
            ['days_ago' => 8, 'type' => 'out', 'qty' => 32],
            ['days_ago' => 5, 'type' => 'in', 'qty' => 55],
            ['days_ago' => 2, 'type' => 'out', 'qty' => 25],
        ];

        foreach ($plan as $index => $row) {
            $stock = $stockPool[$index % $stockPool->count()];
            $qty = (float) $row['qty'];

            // Pastikan stok memang cukup untuk keluar.
            if ($row['type'] === 'out' && (float) $stock->quantity < $qty) {
                continue;
            }

            $date = Carbon::today()
                ->subDays($row['days_ago'])
                ->setTime(9 + ($index % 8), 0);

            $movement = $row['type'] === 'in'
                ? $stockService->increase(
                    product: $stock->product,
                    location: $stock->location,
                    qty: $qty,
                    batch: $stock->batch,
                    referenceType: 'DashboardDemo',
                    referenceId: $index + 1,
                    userId: $admin->id,
                )
                : $stockService->decrease(
                    product: $stock->product,
                    location: $stock->location,
                    qty: $qty,
                    batch: $stock->batch,
                    referenceType: 'DashboardDemo',
                    referenceId: $index + 1,
                    userId: $admin->id,
                );

            $movement->timestamps = false;
            $movement->forceFill(['created_at' => $date, 'updated_at' => $date])->save();
            $movement->timestamps = true;
        }
    }
}
