<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
            SupplierSeeder::class,
            LocationSeeder::class,
            ProductSeeder::class,
            BatchSeeder::class,
            StockInSeeder::class,
            StockOutSeeder::class,
            StockAdjustmentSeeder::class,
            LabelPrintLogSeeder::class,
            DashboardChartSeeder::class,
        ]);
    }
}
