<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $types = ['depot', 'retrait', 'transfert', 'scan'];
        $statuses = ['pending', 'success', 'failed', 'cancelled'];
        $modes = ['ussd', 'qr', 'mobile_app', 'api'];

        return [
            'compte_source_id' => null,
            'compte_destination_id' => null,
            'type' => $this->faker->randomElement($types),
            'montant' => $this->faker->randomFloat(2, 1, 10000),
            'status' => $this->faker->randomElement($statuses),
            'reference' => null,
            'description' => $this->faker->optional()->sentence(),
            'mode' => $this->faker->randomElement($modes),
        ];
    }
}
