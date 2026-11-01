<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Medika Nusantara',
                'phone' => '082112345678',
                'email' => 'sales@medikanusantara.co.id',
                'address' => 'Jl. Mangga Dua Raya No. 12, Jakarta Utara',
            ],
            [
                'name' => 'CV Farma Sehat Mandiri',
                'phone' => '082223456789',
                'email' => 'halo@farmasehatmandiri.id',
                'address' => 'Jl. ABC No. 45, Bandung',
            ],
            [
                'name' => 'PT Anugerah Niaga Supply',
                'phone' => '082334567890',
                'email' => 'purchasing@anugerahniaga.co.id',
                'address' => 'Jl. Rungkut Industri III No. 8, Surabaya',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
