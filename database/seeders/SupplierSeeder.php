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
                'name' => 'PT Supplier A',
                'phone' => '082123456789',
                'email' => 'suppliera@test.com',
                'address' => 'Jln. A No. 1, Jakarta',
            ],
            [
                'name' => 'PT Supplier B',
                'phone' => '082234567890',
                'email' => 'supplierb@test.com',
                'address' => 'Jln. B No. 2, Bandung',
            ],
            [
                'name' => 'CV Supplier C',
                'phone' => '082345678901',
                'email' => 'supplierc@test.com',
                'address' => 'Jln. C No. 3, Surabaya',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
