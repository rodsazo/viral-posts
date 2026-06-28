<?php

namespace Database\Factories;

use App\Enums\PeriodStatus;
use App\Models\Account;
use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Period>
 */
class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->monthName().' '.fake()->year(),
            'status' => PeriodStatus::Borrador,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => PeriodStatus::Publicado]);
    }
}
