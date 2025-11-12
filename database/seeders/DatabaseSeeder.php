<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create some users
        \App\Models\User::factory(10)->create();

        // Seed comptes, operations, liens and code secrets
        $this->call([
            \Database\Seeders\ClientUserSeeder::class,
            \Database\Seeders\OAuthClientSeeder::class,
            \Database\Seeders\CompteSeeder::class,
            \Database\Seeders\OperationSeeder::class,
            \Database\Seeders\LienConnexionSeeder::class,
            \Database\Seeders\CodeSecretSeeder::class,
            \Database\Seeders\TransactionSeeder::class,
        ]);
    }
}
