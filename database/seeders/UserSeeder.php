<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gudangku.test',
            'password' => bcrypt('password'),
            'role' => Role::Admin->value,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Staf Gudang',
            'email' => 'staff@gudangku.test',
            'password' => bcrypt('password'),
            'role' => Role::Staff->value,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Viewer',
            'email' => 'viewer@gudangku.test',
            'password' => bcrypt('password'),
            'role' => Role::Viewer->value,
            'is_active' => true,
        ]);
    }
}
