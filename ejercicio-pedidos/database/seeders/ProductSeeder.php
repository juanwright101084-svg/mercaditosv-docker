<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Crear las categorías si todavía no existen
        $categorias = [
            'Bebidas',
            'Snacks',
            'Postres',
            'Lácteos',
            'Limpieza',
        ];

        foreach ($categorias as $nombre) {
            Category::firstOrCreate(['name' => $nombre]);
        }

        $bebidas = Category::where('name', 'Bebidas')->first();
        $snacks = Category::where('name', 'Snacks')->first();
        $postres = Category::where('name', 'Postres')->first();
        $lacteos = Category::where('name', 'Lácteos')->first();
        $limpieza = Category::where('name', 'Limpieza')->first();

        $products = [
            ['name' => 'Coca Cola 600ml', 'price' => 1.50, 'stock' => 100, 'is_featured' => true, 'category_id' => $bebidas->id, 'image' => '/productos/coca-cola.jpg'],
            ['name' => 'Agua Cristal 1L', 'price' => 0.75, 'stock' => 150, 'is_featured' => false, 'category_id' => $bebidas->id, 'image' => '/productos/agua.jpg'],
            ['name' => 'Jugo Del Valle 500ml', 'price' => 1.25, 'stock' => 80, 'is_featured' => false, 'category_id' => $bebidas->id, 'image' => '/productos/jugo-del-valle.jpg'],
            ['name' => 'Jugo Manzana 500ml', 'price' => 1.30, 'stock' => 75, 'is_featured' => false, 'category_id' => $bebidas->id, 'image' => '/productos/jugo-manzana.jpg'],
            ['name' => 'Fanta Mango 600ml', 'price' => 1.40, 'stock' => 90, 'is_featured' => true, 'category_id' => $bebidas->id, 'image' => '/productos/fanta-mango.jpg'],
            ['name' => 'Doritos Nacho', 'price' => 2.00, 'stock' => 60, 'is_featured' => true, 'category_id' => $snacks->id, 'image' => '/productos/doritos.jpg'],
            ['name' => 'Pringles Original', 'price' => 3.50, 'stock' => 40, 'is_featured' => false, 'category_id' => $snacks->id, 'image' => '/productos/pringles.jpg'],
            ['name' => 'Galletas Oreo', 'price' => 1.80, 'stock' => 90, 'is_featured' => false, 'category_id' => $postres->id, 'image' => '/productos/galletas-oreo.jpg'],
            ['name' => 'Helado Sello Rojo 1L', 'price' => 4.50, 'stock' => 25, 'is_featured' => true, 'category_id' => $postres->id, 'image' => '/productos/helado.jpg'],
            ['name' => 'Leche Foremost 1L', 'price' => 1.10, 'stock' => 70, 'is_featured' => false, 'category_id' => $lacteos->id, 'image' => '/productos/leche-foremost.jpg'],
            ['name' => 'Queso Petit Suisse', 'price' => 2.30, 'stock' => 35, 'is_featured' => false, 'category_id' => $lacteos->id, 'image' => '/productos/queso.jpg'],
            ['name' => 'Detergente Ariel 1kg', 'price' => 3.20, 'stock' => 50, 'is_featured' => false, 'category_id' => $limpieza->id, 'image' => '/productos/detergente.jpg'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}