<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * StockOutSeeder
 *
 * Alur barang keluar yang logis selama ±4 bulan: penjualan rutin ke apotek
 * dan klinik (mingguan), penggunaan internal unit layanan, pemusnahan batch
 * kedaluwarsa, dan satu draft permintaan hari ini.
 */
class StockOutSeeder extends Seeder
{
    use Concerns\GeneratesDocNumbers;

    public function run(): void
    {
        $admin = User::where('email', 'admin@gudangku.test')->firstOrFail();
        $stockService = app(StockService::class);

        $plans = [
            [
                'days_ago' => 110,
                'reason' => 'sale',
                'destination' => 'Apotek Sehat Sejahtera',
                'notes' => 'Penjualan rutin bulanan awal periode',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'VITMK001', 'qty' => 120],
                    ['sku' => 'OBAT001', 'qty' => 80],
                    ['sku' => 'KAN001', 'qty' => 100],
                    ['sku' => 'OBAT002', 'qty' => 50],
                ],
            ],
            [
                'days_ago' => 85,
                'reason' => 'sale',
                'destination' => 'Klinik Bersama Medika',
                'notes' => 'Pesanan langganan klinik mitra',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'VITMK001', 'qty' => 100],
                    ['sku' => 'MKNN001', 'qty' => 30],
                    ['sku' => 'ALS001', 'qty' => 20],
                    ['sku' => 'PRT002', 'qty' => 15],
                ],
            ],
            [
                'days_ago' => 40,
                'reason' => 'sale',
                'destination' => 'Apotek Sehat Sejahtera',
                'notes' => 'Penjualan rutin bulanan',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'VITMK001', 'qty' => 90],
                    ['sku' => 'OBAT001', 'qty' => 70],
                    ['sku' => 'MNM001', 'qty' => 12],
                    ['sku' => 'KAN001', 'qty' => 150],
                ],
            ],
            [
                'days_ago' => 15,
                'reason' => 'sale',
                'destination' => 'Toko Kelontong Berkah',
                'notes' => 'Pesanan ritel mingguan',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'KAN001', 'qty' => 150],
                    ['sku' => 'PRT001', 'qty' => 20],
                    ['sku' => 'ELK001', 'qty' => 3],
                    ['sku' => 'PRT002', 'qty' => 20],
                ],
            ],
            [
                'days_ago' => 5,
                'reason' => 'usage',
                'destination' => 'Ruang Perawatan',
                'notes' => 'Pemakaian internal unit layanan',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'ALS002', 'qty' => 10],
                    ['sku' => 'ATK001', 'qty' => 5],
                ],
            ],
            [
                'days_ago' => 2,
                'reason' => 'expired',
                'destination' => 'Pemusnahan',
                'notes' => 'Pemusnahan batch kedaluwarsa sesuai prosedur',
                'status' => 'posted',
                'include_expired' => true,
                'items' => [
                    ['sku' => 'VITMK001', 'qty' => 20],
                    ['sku' => 'OBAT002', 'qty' => 20],
                ],
            ],
            [
                'days_ago' => 0,
                'reason' => 'usage',
                'destination' => 'Gudang Transit',
                'notes' => 'Draft permintaan barang untuk besok',
                'status' => 'draft',
                'items' => [
                    ['sku' => 'PRT002', 'qty' => 10],
                ],
            ],
        ];

        $docNumbers = $this->planDocNumbers('OUT', $plans);

        foreach ($plans as $index => $plan) {
            $docNumber = $docNumbers[$index];

            if (StockOut::where('doc_number', $docNumber)->exists()) {
                continue;
            }

            $date = Carbon::today()->subDays($plan['days_ago'])->setTime(14, 0);
            $isPosted = $plan['status'] === 'posted';

            $stockOut = StockOut::create([
                'doc_number' => $docNumber,
                'date' => $date->toDateString(),
                'destination' => $plan['destination'],
                'reason' => $plan['reason'],
                'status' => $plan['status'],
                'notes' => $plan['notes'],
                'created_by' => $admin->id,
                'posted_at' => $isPosted ? $date : null,
            ]);

            foreach ($plan['items'] as $item) {
                $product = Product::where('sku', $item['sku'])->firstOrFail();
                $qty = (float) $item['qty'];

                if (! $isPosted) {
                    $stock = Stock::where('product_id', $product->id)->where('quantity', '>', 0)
                        ->orderByDesc('quantity')
                        ->first();

                    StockOutItem::create([
                        'stock_out_id' => $stockOut->id,
                        'product_id' => $product->id,
                        'location_id' => $stock?->location_id ?? Location::orderBy('id')->value('id'),
                        'batch_id' => null,
                        'qty' => $qty,
                    ]);

                    continue;
                }

                $allocations = $this->allocate($product, $qty, (bool) ($plan['include_expired'] ?? false));

                foreach ($allocations as $allocation) {
                    $stock = $allocation['stock'];
                    $take = $allocation['qty'];

                    $movement = $stockService->decrease(
                        product: $product,
                        location: $stock->location,
                        qty: $take,
                        batch: $stock->batch,
                        referenceType: 'StockOut',
                        referenceId: $stockOut->id,
                        userId: $admin->id,
                    );

                    StockOutItem::create([
                        'stock_out_id' => $stockOut->id,
                        'product_id' => $product->id,
                        'location_id' => $stock->location_id,
                        'batch_id' => $stock->batch_id,
                        'qty' => $take,
                    ]);

                    $this->stamp($movement, $date);
                }
            }

            $this->stamp($stockOut, $date);
        }
    }

    /**
     * @return array<int, array{stock: Stock, qty: float}>
     */
    private function allocate(Product $product, float $qty, bool $includeExpired): array
    {
        $available = $this->availableStocks($product, $includeExpired);
        $total = $available->sum(fn (Stock $stock) => (float) $stock->quantity);

        if ($total < $qty) {
            throw new \RuntimeException("Stok tidak cukup untuk {$product->name}");
        }

        $remaining = $qty;
        $allocations = [];

        foreach ($available as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, (float) $stock->quantity);
            $allocations[] = ['stock' => $stock, 'qty' => $take];
            $remaining -= $take;
        }

        return $allocations;
    }

    private function availableStocks(Product $product, bool $includeExpired)
    {
        $today = Carbon::today();

        return Stock::with(['batch', 'location'])
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->when(! $includeExpired, function ($query) use ($today) {
                $query->where(function ($query) use ($today) {
                    $query->whereNull('batch_id')
                        ->orWhereHas('batch', function ($batch) use ($today) {
                            $batch->whereNull('expiry_date')
                                ->orWhereDate('expiry_date', '>=', $today);
                        });
                });
            })
            ->get()
            ->sortBy(fn (Stock $stock) => $stock->batch?->expiry_date?->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    private function stamp($model, Carbon $when): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
        $model->timestamps = true;
    }
}
