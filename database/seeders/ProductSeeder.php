<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Batch;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Produk sample dengan track_batch dan track_expiry
        $products = [
            [
                'sku' => 'VITMK001',
                'barcode' => '8991001234001',
                'name' => 'Vitamin C 500mg Tablet',
                'category_id' => 2, // Vitamin
                'unit_id' => 1, // Pcs
                'min_stock' => 100,
                'purchase_price' => 5000,
                'track_batch' => true,
                'track_expiry' => true,
                'expiry_alert_days' => 30,
                'description' => 'Suplemen vitamin C 500mg per tablet, kemasan botol plastik 100 tablets',
            ],
            [
                'sku' => 'OBAT001',
                'barcode' => '8991001234002',
                'name' => 'Paracetamol 500mg Tablet',
                'category_id' => 1, // Obat-obatan
                'unit_id' => 1, // Pcs
                'min_stock' => 150,
                'purchase_price' => 3000,
                'track_batch' => true,
                'track_expiry' => true,
                'expiry_alert_days' => 30,
                'description' => 'Obat penurun panas dan penghilang nyeri, 1 strip = 10 tablet',
            ],
            [
                'sku' => 'ALS001',
                'barcode' => '8991001234003',
                'name' => 'Hand Sanitizer 500ml',
                'category_id' => 3, // Alat Kesehatan
                'unit_id' => 2, // Box
                'min_stock' => 50,
                'purchase_price' => 25000,
                'track_batch' => true,
                'track_expiry' => true,
                'expiry_alert_days' => 60,
                'description' => 'Desinfektan tangan 500ml, gel alkohol 70%',
            ],
            [
                'sku' => 'KAN001',
                'barcode' => '8991001234004',
                'name' => 'Kantong Plastik Putih (Kecil)',
                'category_id' => 8, // Produk Rumah Tangga
                'unit_id' => 3, // Dus
                'min_stock' => 500,
                'purchase_price' => 50000,
                'track_batch' => false,
                'track_expiry' => false,
                'description' => '1 dus berisi 1000 lembar kantong plastik ukuran 20x30cm',
            ],
            [
                'sku' => 'MKNN001',
                'barcode' => '8991001234005',
                'name' => 'Masker Medis 3ply (50 pcs)',
                'category_id' => 3, // Alat Kesehatan
                'unit_id' => 1, // Pcs
                'min_stock' => 200,
                'purchase_price' => 45000,
                'track_batch' => true,
                'track_expiry' => true,
                'expiry_alert_days' => 45,
                'description' => 'Masker medis 3 lapis, earloop, kemasan 50 pcs per box',
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Buat batch jika track_batch = true
            if ($product->track_batch) {
                $batches = [
                    [
                        'batch_number' => 'B-261001-01',
                        'supplier_id' => 1,
                        'manufactured_date' => Carbon::now()->subDays(60)->toDateString(),
                        'expiry_date' => Carbon::now()->addDays(90)->toDateString(), // 90 hari ke depan
                        'status' => 'active',
                    ],
                    [
                        'batch_number' => 'B-261001-02',
                        'supplier_id' => 2,
                        'manufactured_date' => Carbon::now()->subDays(30)->toDateString(),
                        'expiry_date' => Carbon::now()->addDays(15)->toDateString(), // 15 hari (near expiry)
                        'status' => 'near_expiry',
                    ],
                    [
                        'batch_number' => 'B-261001-03',
                        'supplier_id' => 3,
                        'manufactured_date' => Carbon::now()->subDays(120)->toDateString(),
                        'expiry_date' => Carbon::now()->subDays(5)->toDateString(), // Sudah expired
                        'status' => 'expired',
                    ],
                ];

                foreach ($batches as $batchData) {
                    $batch = Batch::create(array_merge(['product_id' => $product->id], $batchData));

                    // Buat stock per batch per location
                    if ($batchData['status'] !== 'expired') {
                        Stock::create([
                            'product_id' => $product->id,
                            'location_id' => 1, // RAK-A1
                            'batch_id' => $batch->id,
                            'quantity' => rand(100, 500),
                        ]);

                        Stock::create([
                            'product_id' => $product->id,
                            'location_id' => 2, // RAK-A2
                            'batch_id' => $batch->id,
                            'quantity' => rand(50, 200),
                        ]);
                    }
                }
            } else {
                // Untuk produk tanpa batch tracking, buat stock langsung
                Stock::create([
                    'product_id' => $product->id,
                    'location_id' => 3, // RAK-B1
                    'batch_id' => null,
                    'quantity' => rand(500, 2000),
                ]);
            }
        }
    }
}
