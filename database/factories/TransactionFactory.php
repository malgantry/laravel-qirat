<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'category' => $this->faker->word,
            'occurred_at' => now(),
            'note' => $this->faker->sentence,
        ];
    }
}
