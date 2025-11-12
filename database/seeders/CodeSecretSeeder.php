<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CodeSecret;

class CodeSecretSeeder extends Seeder
{
    public function run(): void
    {
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                CodeSecret::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
