<?php

namespace Database\Factories;

use App\Enums\CtaCategory;
use App\Models\Account;
use App\Models\ContentCta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentCta>
 */
class ContentCtaFactory extends Factory
{
    protected $model = ContentCta::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'category' => fake()->randomElement(CtaCategory::cases()),
            'text' => fake()->sentence(),
        ];
    }
}
