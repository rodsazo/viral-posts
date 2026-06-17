<?php

namespace Database\Factories;

use App\Models\ViralReferent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViralReferent>
 */
class ViralReferentFactory extends Factory
{
    protected $model = ViralReferent::class;

    public function definition(): array
    {
        return [
            'niche_id' => null,
            'name' => fake()->name(),
            'notes' => fake()->optional()->sentence(),
            'instagram_url' => 'https://instagram.com/'.fake()->userName(),
        ];
    }
}
