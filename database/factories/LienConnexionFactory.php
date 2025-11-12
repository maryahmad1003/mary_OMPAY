<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\LienConnexion;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LienConnexion>
 */
class LienConnexionFactory extends Factory
{
    protected $model = LienConnexion::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'token' => Str::random(20),
            'expires_at' => now()->addMinutes(30),
            'is_used' => false,
        ];
    }
}
