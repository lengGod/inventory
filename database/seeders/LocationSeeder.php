<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['code' => 'RAK-A1', 'name' => 'Rak A Baris 1', 'barcode' => 'LOC001'],
            ['code' => 'RAK-A2', 'name' => 'Rak A Baris 2', 'barcode' => 'LOC002'],
            ['code' => 'RAK-B1', 'name' => 'Rak B Baris 1', 'barcode' => 'LOC003'],
            ['code' => 'RAK-B2', 'name' => 'Rak B Baris 2', 'barcode' => 'LOC004'],
            ['code' => 'ZONA-C', 'name' => 'Zona Dingin', 'barcode' => 'LOC005'],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(['code' => $location['code']], $location);
        }
    }
}
