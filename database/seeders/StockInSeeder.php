<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * StockInSeeder
 *
 * Riwayat penerimaan barang ±4 bulan. Setiap penerimaan memakai supplier yang
 * sesuai kategorinya (farmasi -> distributor farmasi, ATK/harian -> supplier
 * umum) dengan nomor dokumen mengikuti bulan tanggal transaksi, bukan bulan seed.
 */
class StockInSeeder extends Seeder
{
    use Concerns\GeneratesDocNumbers;

    public function run(): void
    {
        $admin = User::where('email', 'admin@gudangku.test')->firstOrFail();
        $stockService = app(StockService::class);

        $plans = [
            [
                'days_ago' => 120,
                'supplier' => 'PT Medika Nusantara',
                'notes' => 'Pembelian awal untuk stok gudang',
                'items' => [
                    ['sku' => 'VITMK001', 'batch' => 'active', 'location' => 'RAK-A1', 'qty' => 400],
                    ['sku' => 'OBAT001', 'batch' => 'active', 'location' => 'RAK-A1', 'qty' => 200],
                    ['sku' => 'OBAT002', 'batch' => 'active', 'location' => 'RAK-A2', 'qty' => 120],
                    ['sku' => 'ALS001', 'batch' => 'active', 'location' => 'RAK-A1', 'qty' => 48],
                    ['sku' => 'MKNN001', 'batch' => 'active', 'location' => 'RAK-A2', 'qty' => 150],
                    ['sku' => 'KAN001', 'batch' => null, 'location' => 'RAK-B1', 'qty' => 300],
                ],
            ],
            [
                'days_ago' => 90,
                'supplier' => 'CV Farma Sehat Mandiri',
                'notes' => 'Pengadaan produk farmasi dan rumah tangga',
                'items' => [
                    ['sku' => 'VITMK002', 'batch' => 'active', 'location' => 'RAK-A2', 'qty' => 60],
                    ['sku' => 'PRT002', 'batch' => 'active', 'location' => 'RAK-B2', 'qty' => 60],
                    ['sku' => 'ALS002', 'batch' => 'active', 'location' => 'RAK-B1', 'qty' => 60],
                    ['sku' => 'PRT001', 'batch' => null, 'location' => 'RAK-B2', 'qty' => 40],
                    ['sku' => 'MNM001', 'batch' => 'active', 'location' => 'RAK-B1', 'qty' => 24],
                ],
            ],
            [
                'days_ago' => 45,
                'supplier' => 'PT Anugerah Niaga Supply',
                'notes' => 'Penerimaan barang non-farmasi rutin',
                'items' => [
                    ['sku' => 'KAN001', 'batch' => null, 'location' => 'RAK-B1', 'qty' => 400],
                    ['sku' => 'ATK001', 'batch' => null, 'location' => 'RAK-B1', 'qty' => 60],
                    ['sku' => 'ELK001', 'batch' => null, 'location' => 'RAK-B1', 'qty' => 12],
                    ['sku' => 'PKN001', 'batch' => null, 'location' => 'RAK-B2', 'qty' => 30],
                ],
            ],
            [
                'days_ago' => 25,
                'supplier' => 'PT Medika Nusantara',
                'notes' => 'Penerimaan rutin bulan ini',
                'items' => [
                    ['sku' => 'VITMK001', 'batch' => 'active', 'location' => 'RAK-A1', 'qty' => 500],
                    ['sku' => 'OBAT001', 'batch' => 'active', 'location' => 'RAK-A1', 'qty' => 150],
                    ['sku' => 'ALS001', 'batch' => 'active', 'location' => 'RAK-A1', 'qty' => 48],
                    ['sku' => 'MKNN001', 'batch' => 'active', 'location' => 'RAK-A2', 'qty' => 200],
                    ['sku' => 'OBAT002', 'batch' => 'active', 'location' => 'RAK-A2', 'qty' => 150],
                ],
            ],
            [
                'days_ago' => 12,
                'supplier' => 'CV Farma Sehat Mandiri',
                'notes' => 'Penerimaan suplemen dan obat jangka pendek',
                'items' => [
                    ['sku' => 'VITMK001', 'batch' => 'near', 'location' => 'RAK-A1', 'qty' => 150],
                    ['sku' => 'OBAT001', 'batch' => 'near', 'location' => 'RAK-A2', 'qty' => 60],
                    ['sku' => 'VITMK002', 'batch' => 'active', 'location' => 'RAK-A2', 'qty' => 80],
                    ['sku' => 'ALS002', 'batch' => 'active', 'location' => 'RAK-B1', 'qty' => 40],
                    ['sku' => 'MNM001', 'batch' => 'near', 'location' => 'RAK-B1', 'qty' => 12],
                ],
            ],
            [
                'days_ago' => 7,
                'supplier' => 'PT Anugerah Niaga Supply',
                'notes' => 'Penerimaan campuran disertai penggantian batch lama',
                'items' => [
                    ['sku' => 'MKNN001', 'batch' => 'near', 'location' => 'RAK-A2', 'qty' => 120],
                    ['sku' => 'VITMK001', 'batch' => 'expired', 'location' => 'RAK-A1', 'qty' => 50],
                    ['sku' => 'OBAT002', 'batch' => 'expired', 'location' => 'RAK-A2', 'qty' => 40],
                ],
            ],
        ];

        $docNumbers = $this->planDocNumbers('IN', $plans);

        foreach ($plans as $index => $plan) {
            $docNumber = $docNumbers[$index];

            if (StockIn::where('doc_number', $docNumber)->exists()) {
                continue;
            }

            $date = Carbon::today()->subDays($plan['days_ago'])->setTime(9, 0);
            $supplier = Supplier::where('name', $plan['supplier'])->first();

            $stockIn = StockIn::create([
                'doc_number' => $docNumber,
                'date' => $date->toDateString(),
                'supplier_id' => $supplier?->id,
                'status' => 'posted',
                'notes' => $plan['notes'],
                'created_by' => $admin->id,
                'posted_at' => $date,
            ]);

            foreach ($plan['items'] as $item) {
                $product = Product::where('sku', $item['sku'])->firstOrFail();
                $location = Location::where('code', $item['location'])->firstOrFail();
                $batch = $this->resolveBatch($product, $item['batch']);
                $qty = (float) $item['qty'];

                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'batch_id' => $batch?->id,
                    'qty' => $qty,
                    'unit_price' => $product->purchase_price,
                ]);

                $movement = $stockService->increase(
                    product: $product,
                    location: $location,
                    qty: $qty,
                    batch: $batch,
                    referenceType: 'StockIn',
                    referenceId: $stockIn->id,
                    userId: $admin->id,
                );

                $this->stamp($movement, $date);
            }

            $this->stamp($stockIn, $date);
        }
    }

    private function resolveBatch(Product $product, ?string $hint): ?Batch
    {
        if (! $product->track_batch) {
            return null;
        }

        $today = Carbon::today();
        $query = Batch::where('product_id', $product->id);

        $batch = match ($hint) {
            'expired' => (clone $query)->whereDate('expiry_date', '<', $today)->orderBy('expiry_date')->first(),
            'near' => (clone $query)
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $today->copy()->addDays(30))
                ->orderBy('expiry_date')
                ->first(),
            'active' => (clone $query)->whereDate('expiry_date', '>', $today->copy()->addDays(30))->orderBy('expiry_date')->first(),
            default => (clone $query)->orderBy('expiry_date')->first(),
        };

        return $batch ?? $query->orderBy('expiry_date')->first();
    }

    private function stamp($model, Carbon $when): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
        $model->timestamps = true;
    }
}
