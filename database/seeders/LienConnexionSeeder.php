<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LienConnexion;

class LienConnexionSeeder extends Seeder
{
    public function run(): void
    {
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                // create one unused connexion token for each user
                LienConnexion::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
