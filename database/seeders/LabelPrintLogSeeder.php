<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\LabelPrintLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class LabelPrintLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gudangku.test')->first();
        $staff = User::where('email', 'staff@gudangku.test')->first();

        if (! $admin || ! $staff) {
            return;
        }

        $product = Product::where('sku', 'VITMK001')->first();
        $batch = $product
            ? Batch::where('product_id', $product->id)->orderBy('id')->first()
            : null;
        $location = Location::where('code', 'RAK-A1')->first();

        $plans = [
            [
                'label_type' => 'product',
                'reference_id' => $product?->id,
                'copies' => 12,
                'printed_by' => $admin->id,
            ],
            [
                'label_type' => 'batch',
                'reference_id' => $batch?->id,
                'copies' => 8,
                'printed_by' => $staff->id,
            ],
            [
                'label_type' => 'location',
                'reference_id' => $location?->id,
                'copies' => 4,
                'printed_by' => $staff->id,
            ],
        ];

        foreach ($plans as $index => $plan) {
            if (! $plan['reference_id']) {
                continue;
            }

            $log = LabelPrintLog::firstOrCreate($plan);

            if ($log->wasRecentlyCreated) {
                $log->forceFill([
                    'created_at' => now()->subMinutes(15 * ($index + 1)),
                ])->saveQuietly();
            }
        }
    }
}
