<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Capture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Capture>
 */
class CaptureFactory extends Factory
{
    protected $model = Capture::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
