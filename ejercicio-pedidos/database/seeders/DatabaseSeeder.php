<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'juan@test.com'],
            [
                'name' => 'Juan Guzman',
                'password' => 'password123',
                'role' => 'admin',
            ]
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ClientSeeder::class,
        ]);
    }
}