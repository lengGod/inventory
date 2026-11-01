<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $products = Product::where('track_batch', true)->get();

        foreach ($products as $index => $product) {
            // Batch aktif: kedaluwarsa jauh di depan
            $this->createBatch(
                $product,
                $today->copy()->subDays(60),
                $today->copy()->addDays($index % 2 === 0 ? 120 : 180),
                BatchStatus::Active,
                1,
            );

            // Batch mendekati kedaluwarsa (<= 30 hari)
            $this->createBatch(
                $product,
                $today->copy()->subDays(30),
                $today->copy()->addDays(15),
                BatchStatus::NearExpiry,
                2,
            );

            // Batch kedaluwarsa untuk sebagian produk
            if (in_array($product->sku, ['VITMK001', 'OBAT002', 'MNM001'], true)) {
                $this->createBatch(
                    $product,
                    $today->copy()->subDays(180),
                    $today->copy()->subDays(5),
                    BatchStatus::Expired,
                    3,
                );
            }

            // Contoh batch habis (tanpa stok) untuk satu produk
            if ($product->sku === 'VITMK002') {
                $this->createBatch(
                    $product,
                    $today->copy()->subDays(240),
                    $today->copy()->subDays(30),
                    BatchStatus::Depleted,
                    4,
                );
            }
        }
    }

    private function createBatch(
        Product $product,
        Carbon $manufacturedDate,
        Carbon $expiryDate,
        BatchStatus $status,
        int $sequence,
    ): void {
        $batchNumber = sprintf(
            'B-%s-%02d',
            $manufacturedDate->format('ymd'),
            $sequence,
        );

        Batch::updateOrCreate(
            [
                'product_id' => $product->id,
                'batch_number' => $batchNumber,
            ],
            [
                'supplier_id' => (($product->id + $sequence) % 3) + 1,
                'manufactured_date' => $manufacturedDate->toDateString(),
                'expiry_date' => $expiryDate->toDateString(),
                'status' => $status->value,
                'notes' => null,
            ]
        );
    }
}
