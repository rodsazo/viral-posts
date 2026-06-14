<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\IdealFollower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdealFollower>
 */
class IdealFollowerFactory extends Factory
{
    protected $model = IdealFollower::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
        ];
    }
}
