<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CodeSecret;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CodeSecret>
 */
class CodeSecretFactory extends Factory
{
    protected $model = CodeSecret::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'code' => $this->faker->regexify('[0-9]{6}'),
            'is_active' => true,
        ];
    }
}
