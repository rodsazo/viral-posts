<?php

namespace Database\Factories;

use App\Enums\BeliefType;
use App\Models\Account;
use App\Models\Belief;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Belief>
 */
class BeliefFactory extends Factory
{
    protected $model = Belief::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'type' => fake()->randomElement(BeliefType::cases()),
            'statement' => fake()->sentence(),
            'stance' => fake()->optional()->paragraph(),
        ];
    }

    public function myth(): static
    {
        return $this->state(['type' => BeliefType::Myth]);
    }

    public function truth(): static
    {
        return $this->state(['type' => BeliefType::Truth]);
    }
}
