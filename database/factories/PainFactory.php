<?php

namespace Database\Factories;

use App\Enums\PainType;
use App\Models\IdealFollower;
use App\Models\Pain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pain>
 */
class PainFactory extends Factory
{
    protected $model = Pain::class;

    public function definition(): array
    {
        // Un dolor siempre cuelga de un seguidor ideal; por defecto, su misma marca.
        $follower = IdealFollower::factory()->create();

        return [
            'account_id' => $follower->account_id,
            'ideal_follower_id' => $follower->id,
            'type' => fake()->randomElement(PainType::cases()),
            'body' => fake()->sentence(),
        ];
    }
}
