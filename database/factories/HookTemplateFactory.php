<?php

namespace Database\Factories;

use App\Models\HookTemplate;
use App\Models\ViralReferent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HookTemplate>
 */
class HookTemplateFactory extends Factory
{
    protected $model = HookTemplate::class;

    public function definition(): array
    {
        return [
            'viral_referent_id' => ViralReferent::factory(),
            'name' => fake()->unique()->words(2, true),
            'objective' => fake()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'example_generic' => fake()->sentence(),
            'reference_url' => fake()->optional()->url(),
            'real_examples' => [fake()->url(), fake()->url()],
            'icon' => 'fa-solid fa-fire',
        ];
    }
}
