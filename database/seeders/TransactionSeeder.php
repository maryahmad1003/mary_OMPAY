<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Compte::chunk(100, function ($comptes) {
            foreach ($comptes as $compte) {
                // create some inbound/outbound transactions
                Transaction::factory()->count(5)->create([
                    'compte_source_id' => $compte->id,
                ]);
            }
        });
    }
}
