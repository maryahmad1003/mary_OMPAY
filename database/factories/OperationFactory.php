<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Operation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operation>
 */
class OperationFactory extends Factory
{
    protected $model = Operation::class;

    public function definition(): array
    {
        $types = ['depot', 'retrait', 'transfert', 'scan'];

        return [
            'compte_id' => null,
            'type' => $this->faker->randomElement($types),
            'montant' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->optional()->sentence(),
            'destination_compte_id' => null,
        ];
    }
}
