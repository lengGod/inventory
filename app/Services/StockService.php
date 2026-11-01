<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Enums\MovementType;
use Illuminate\Support\Facades\DB;

/**
 * StockService
 * 
 * Logika inti perubahan stok. Semua perubahan stok wajib lewat service ini.
 * Setiap operasi menggunakan transaction + lock untuk mencegah race condition.
 */
class StockService
{
    /**
     * Tambah stok (barang masuk)
     * 
     * @param Product $product
     * @param Location $location
     * @param int|float $qty
     * @param ?Batch $batch
     * @param string $referenceType
     * @param int $referenceId
     * @param int $userId
     * 
     * @return StockMovement
     */
    public function increase(
        Product $product,
        Location $location,
        float $qty,
        ?Batch $batch = null,
        string $referenceType = '',
        int $referenceId = 0,
        int $userId = 0,
    ): StockMovement {
        return DB::transaction(function () use ($product, $location, $qty, $batch, $referenceType, $referenceId, $userId) {
            $stock = Stock::query()
                ->where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->where('batch_id', $batch?->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'batch_id' => $batch?->id,
                ], ['quantity' => 0]);

            $stock->quantity += $qty;
            $stock->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'batch_id' => $batch?->id,
                'type' => MovementType::In->value,
                'qty' => $qty,
                'balance_after' => $stock->quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Kurangi stok (barang keluar)
     * 
     * @param Product $product
     * @param Location $location
     * @param int|float $qty
     * @param ?Batch $batch
     * @param string $referenceType
     * @param int $referenceId
     * @param int $userId
     * 
     * @return StockMovement
     * @throws \Exception Jika stok tidak cukup
     */
    public function decrease(
        Product $product,
        Location $location,
        float $qty,
        ?Batch $batch = null,
        string $referenceType = '',
        int $referenceId = 0,
        int $userId = 0,
    ): StockMovement {
        return DB::transaction(function () use ($product, $location, $qty, $batch, $referenceType, $referenceId, $userId) {
            $stock = Stock::query()
                ->where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->where('batch_id', $batch?->id)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->quantity < $qty) {
                throw new \Exception("Stok tidak cukup untuk {$product->name}");
            }

            $stock->quantity -= $qty;
            $stock->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'batch_id' => $batch?->id,
                'type' => MovementType::Out->value,
                'qty' => -$qty,
                'balance_after' => $stock->quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Adjust stok (opname)
     * 
     * @param Product $product
     * @param Location $location
     * @param float $diff
     * @param ?Batch $batch
     * @param string $reason
     * @param int $userId
     * 
     * @return StockMovement
     */
    public function adjust(
        Product $product,
        Location $location,
        float $diff,
        ?Batch $batch = null,
        string $reason = '',
        int $userId = 0,
    ): StockMovement {
        return DB::transaction(function () use ($product, $location, $diff, $batch, $reason, $userId) {
            $stock = Stock::query()
                ->where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->where('batch_id', $batch?->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'batch_id' => $batch?->id,
                ], ['quantity' => 0]);

            $stock->quantity += $diff;
            $stock->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'batch_id' => $batch?->id,
                'type' => MovementType::Adjust->value,
                'qty' => $diff,
                'balance_after' => $stock->quantity,
                'reference_type' => 'StockAdjustment',
                'reference_id' => 0,
                'created_by' => $userId,
            ]);
        });
    }
}
