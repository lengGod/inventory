<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Pcs', 'short_name' => 'Pcs'],
            ['name' => 'Box', 'short_name' => 'Box'],
            ['name' => 'Dus', 'short_name' => 'Dus'],
            ['name' => 'Kilogram', 'short_name' => 'Kg'],
            ['name' => 'Gram', 'short_name' => 'G'],
            ['name' => 'Liter', 'short_name' => 'L'],
            ['name' => 'Mililiter', 'short_name' => 'Ml'],
            ['name' => 'Meter', 'short_name' => 'M'],
            ['name' => 'Jam', 'short_name' => 'Jam'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
