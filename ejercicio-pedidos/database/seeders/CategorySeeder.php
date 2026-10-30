<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Bebidas', 'Snacks', 'Postres', 'Lácteos', 'Limpieza'];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}