<?php

namespace Database\Factories;

use App\Models\HerasTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HerasTemplate>
 */
class HerasTemplateFactory extends Factory
{
    protected $model = HerasTemplate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->catchPhrase(),
            'structure' => fake()->paragraph(),
            'suggested_format' => fake()->word(),
            'viral_mechanism' => fake()->word(),
        ];
    }
}
