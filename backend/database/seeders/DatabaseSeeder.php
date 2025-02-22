<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\Admin;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // CLIENTS 
        $clients = [
            ['name_client' => 'Client 1', 'numero_client' => '0349049881'],
            ['name_client' => 'Client 2', 'numero_client' => '0381034567'],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }

        // ADMIN
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin')
        ]);
    }
}
