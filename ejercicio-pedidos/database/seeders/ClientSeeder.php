<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Cliente Prueba', 'email' => 'cliente@test.com'],
            ['name' => 'Ana Perez', 'email' => 'ana@test.com'],
            ['name' => 'Carlos Rodriguez', 'email' => 'carlos@test.com'],
        ];

        foreach ($clients as $client) {
            Client::firstOrCreate(['email' => $client['email']], $client);
        }
    }
}