<?php

namespace Database\Factories;

use App\Enums\BeliefType;
use App\Models\Belief;
use App\Models\IdealFollower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Belief>
 */
class BeliefFactory extends Factory
{
    protected $model = Belief::class;

    public function definition(): array
    {
        // Toda creencia cuelga de un seguidor ideal; por defecto, su misma marca.
        $follower = IdealFollower::factory()->create();

        return [
            'account_id' => $follower->account_id,
            'ideal_follower_id' => $follower->id,
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
