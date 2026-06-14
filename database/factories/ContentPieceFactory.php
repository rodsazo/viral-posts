<?php

namespace Database\Factories;

use App\Enums\ContentFormat;
use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\ContentPiece;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentPiece>
 */
class ContentPieceFactory extends Factory
{
    protected $model = ContentPiece::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'winning_idea_id' => null,
            'title' => fake()->sentence(4),
            'format' => fake()->randomElement(ContentFormat::cases()),
            'status' => ContentStatus::Planificacion,
            'hook' => fake()->optional()->sentence(),
            'story' => fake()->optional()->paragraph(),
            'moral' => fake()->optional()->sentence(),
            'cta' => fake()->optional()->sentence(),
            'url' => null,
            'rating' => null,
        ];
    }
}
