<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'target_amount' => 1000,
            'current_amount' => 0,
            'deadline' => now()->addMonths(6),
        ];
    }
}
