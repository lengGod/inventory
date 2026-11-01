<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;

/**
 * ScanLookupService
 * 
 * Resolusi kode scan: barcode → SKU → batch → lokasi
 * Urutan prioritas sesuai PRD section 5.23
 */
class ScanLookupService
{
    public function lookup(string $code): ?array
    {
        // 1. Cek barcode produk
        $product = Product::where('barcode', $code)
            ->where('is_active', true)
            ->first();

        if ($product) {
            return $this->productResult($product);
        }

        // 2. Cek SKU
        $product = Product::where('sku', $code)
            ->where('is_active', true)
            ->first();

        if ($product) {
            return $this->productResult($product);
        }

        // 3. Cek batch number
        $batch = Batch::where('batch_number', $code)->first();

        if ($batch) {
            return $this->batchResult($batch);
        }

        // 4. Cek lokasi/rak barcode
        $location = Location::where('barcode', $code)->first();

        if ($location) {
            return $this->locationResult($location);
        }

        return null;
    }

    private function productResult(Product $product): array
    {
        return [
            'type' => 'product',
            'product' => $product,
            'stocks' => $product->stocks()
                ->with(['location', 'batch'])
                ->where('quantity', '>', 0)
                ->get(),
        ];
    }

    private function batchResult(Batch $batch): array
    {
        return [
            'type' => 'batch',
            'batch' => $batch,
            'product' => $batch->product,
            'stocks' => $batch->stocks()
                ->with('location')
                ->where('quantity', '>', 0)
                ->get(),
        ];
    }

    private function locationResult(Location $location): array
    {
        return [
            'type' => 'location',
            'location' => $location,
            'stocks' => $location->stocks()
                ->with(['product', 'batch'])
                ->where('quantity', '>', 0)
                ->get(),
        ];
    }
}
