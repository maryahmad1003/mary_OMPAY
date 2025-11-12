<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Compte;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a compte for each existing user if not exists
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                if (! $user->compte) {
                    Compte::factory()->create([
                        'user_id' => $user->id,
                    ]);
                }
            }
        });
    }
}
