<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Operation;

class OperationSeeder extends Seeder
{
    public function run(): void
    {
        // For each compte create a few random operations
        Compte::chunk(100, function ($comptes) {
            foreach ($comptes as $compte) {
                Operation::factory()->count(5)->create([
                    'compte_id' => $compte->id,
                ]);
            }
        });
    }
}
