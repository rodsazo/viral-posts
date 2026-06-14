<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\IdealFollower;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            // El seguidor ideal debe vivir en la misma cuenta que la pregunta.
            'ideal_follower_id' => fn (array $attrs) => IdealFollower::factory()
                ->create(['account_id' => $attrs['account_id']])
                ->getKey(),
            'category_id' => null,
            'body' => fake()->sentence().'?',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
