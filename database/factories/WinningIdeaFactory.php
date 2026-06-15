<?php

namespace Database\Factories;

use App\Enums\ViralMechanism;
use App\Models\Account;
use App\Models\WinningIdea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WinningIdea>
 */
class WinningIdeaFactory extends Factory
{
    protected $model = WinningIdea::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'title' => fake()->catchPhrase(),
            'concept' => fake()->paragraph(),
            'viral_mechanism' => fake()->optional()->randomElement(ViralMechanism::cases()),
        ];
    }
}
