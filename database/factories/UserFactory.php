<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition()
    {
        return [
            // si vous utilisez UUID sur users :
            'id' => (string) Str::uuid(),
            // champs existants — adaptez noms (name/nom/prenom) selon votre modèle
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),

            // AJOUT: générer un téléphone unique (ex: préfixe 221 pour Sénégal)
            'telephone' => $this->faker->unique()->numerify('2217########'),

            // autres champs optionnels
            'status' => 'client',
            'is_verified' => true,
        ];
    }
}
