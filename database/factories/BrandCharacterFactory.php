<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\BrandCharacter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BrandCharacter>
 */
class BrandCharacterFactory extends Factory
{
    protected $model = BrandCharacter::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->firstName(),
            'essence' => fake()->sentence(),
            'archetype' => 'El amigo que ya jugó',
            'enemy_abstract' => fake()->sentence(),
            'postures' => [
                ['statement' => fake()->sentence(), 'why' => fake()->sentence(), 'kind' => 'principal', 'bridge' => false],
                ['statement' => fake()->sentence(), 'why' => fake()->sentence(), 'kind' => 'principal', 'bridge' => false],
                ['statement' => fake()->sentence(), 'why' => fake()->sentence(), 'kind' => 'secundaria', 'bridge' => true],
            ],
            'origin_full' => fake()->paragraph(),
            'voice_tone' => 'Cercano y entusiasta',
            'verbal_signature' => fake()->sentence(),
            'coherence_rules' => [fake()->sentence(), fake()->sentence()],
        ];
    }
}
