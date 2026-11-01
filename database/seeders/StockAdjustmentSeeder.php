<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StockAdjustmentSeeder extends Seeder
{
    use Concerns\GeneratesDocNumbers;

    public function run(): void
    {
        $admin = User::where('email', 'admin@gudangku.test')->firstOrFail();
        $stockService = app(StockService::class);

        $plans = [
            [
                'days_ago' => 60,
                'reason' => 'Koreksi Sistem',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'VITMK001', 'diff' => 5],
                    ['sku' => 'VITMK002', 'diff' => 2],
                ],
            ],
            [
                'days_ago' => 33,
                'reason' => 'Barang Rusak',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'MKNN001', 'diff' => -3],
                    ['sku' => 'ALS002', 'diff' => -1],
                ],
            ],
            [
                'days_ago' => 10,
                'reason' => 'Koreksi Opname',
                'status' => 'posted',
                'items' => [
                    ['sku' => 'ATK001', 'diff' => -5],
                    ['sku' => 'PRT001', 'diff' => -3],
                ],
            ],
            [
                'days_ago' => 0,
                'reason' => 'Koreksi Opname',
                'status' => 'draft',
                'items' => [
                    ['sku' => 'PRT002', 'diff' => 10],
                ],
            ],
        ];

        $docNumbers = $this->planDocNumbers('ADJ', $plans);

        foreach ($plans as $index => $plan) {
            $docNumber = $docNumbers[$index];

            if (StockAdjustment::where('doc_number', $docNumber)->exists()) {
                continue;
            }

            $date = Carbon::today()->subDays($plan['days_ago'])->setTime(10, 0);
            $isPosted = $plan['status'] === 'posted';

            $adjustment = StockAdjustment::create([
                'doc_number' => $docNumber,
                'date' => $date->toDateString(),
                'status' => $plan['status'],
                'reason' => $plan['reason'],
                'created_by' => $admin->id,
                'approved_by' => $isPosted ? $admin->id : null,
                'approved_at' => $isPosted ? $date : null,
            ]);

            foreach ($plan['items'] as $item) {
                $product = Product::where('sku', $item['sku'])->firstOrFail();
                $stock = Stock::where('product_id', $product->id)
                    ->where('quantity', '>', 0)
                    ->orderByDesc('quantity')
                    ->first();

                $location = $stock?->location ?? Location::orderBy('id')->firstOrFail();
                $batch = $stock?->batch_id ? Batch::find($stock->batch_id) : null;
                $systemQty = (float) ($stock?->quantity ?? 0);

                $diff = (float) $item['diff'];
                $physicalQty = $systemQty + $diff;

                if ($physicalQty < 0) {
                    $physicalQty = 0;
                    $diff = -$systemQty;
                }

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'batch_id' => $batch?->id,
                    'system_qty' => $systemQty,
                    'physical_qty' => $physicalQty,
                    'diff' => $diff,
                    'reason' => $plan['reason'],
                ]);

                if (! $isPosted || $diff === 0.0) {
                    continue;
                }

                $movement = $stockService->adjust(
                    product: $product,
                    location: $location,
                    diff: $diff,
                    batch: $batch,
                    reason: $plan['reason'],
                    userId: $admin->id,
                );

                $movement->forceFill(['reference_id' => $adjustment->id])->save();

                $this->stamp($movement, $date);
            }

            $this->stamp($adjustment, $date);
        }
    }

    private function stamp($model, Carbon $when): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
        $model->timestamps = true;
    }
}

