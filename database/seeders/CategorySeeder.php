<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Obat-obatan',
            'Vitamin',
            'Alat Kesehatan',
            'Makanan & Minuman',
            'Perlengkapan Kantor',
            'Elektronik',
            'Pakaian',
            'Produk Rumah Tangga',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
