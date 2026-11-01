<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * BatchAllocationService
 * 
 * Alokasi batch FEFO (First Expired, First Out)
 * Batch dengan kedaluwarsa terdekat didahulukan.
 * Jika kedaluwarsa sama, urut FIFO (tanggal terima).
 */
class BatchAllocationService
{
    /**
     * Dapatkan batch untuk pengeluaran FEFO
     * 
     * @param Product $product
     * @param Location $location
     * @param float $requiredQty
     * @param bool $includeExpired
     * 
     * @return Collection Batch allocation dengan qty
     */
    public function allocate(
        Product $product,
        Location $location,
        float $requiredQty,
        bool $includeExpired = false,
    ): Collection {
        $query = Batch::query()
            ->where('product_id', $product->id)
            ->whereHas('stocks', function ($q) use ($location) {
                $q->where('location_id', $location->id)
                    ->where('quantity', '>', 0);
            });

        if (!$includeExpired) {
            $query->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', Carbon::today());
            });
        }

        $batches = $query
            ->orderBy('expiry_date', 'asc')
            ->orderBy('manufactured_date', 'asc')
            ->get()
            ->map(function (Batch $batch) use ($location) {
                $stock = $batch->stocks()
                    ->where('location_id', $location->id)
                    ->first();
                
                return [
                    'batch' => $batch,
                    'available_qty' => $stock?->quantity ?? 0,
                    'allocated_qty' => 0,
                ];
            });

        // Alokasikan qty sesuai tersedia
        $remaining = $requiredQty;
        foreach ($batches as &$alloc) {
            if ($remaining <= 0) {
                break;
            }

            $allocate = min($remaining, $alloc['available_qty']);
            $alloc['allocated_qty'] = $allocate;
            $remaining -= $allocate;
        }

        return collect($batches)->filter(fn ($a) => $a['allocated_qty'] > 0);
    }

    /**
     * Cek apakah batch kedaluwarsa
     * 
     * @param Batch $batch
     * 
     * @return bool
     */
    public function isExpired(Batch $batch): bool
    {
        if (!$batch->expiry_date) {
            return false;
        }

        return $batch->expiry_date->isPast();
    }
}
