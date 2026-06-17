<?php

namespace Database\Factories;

use App\Models\Niche;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Niche>
 */
class NicheFactory extends Factory
{
    protected $model = Niche::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->hexColor(),
        ];
    }
}
