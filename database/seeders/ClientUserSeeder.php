<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ClientUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'telephone' => '221770000000',
            'nom' => 'Client',
            'prenom' => 'Test',
            'status' => 'client',
            'email' => null,
        ]);
    }
}
