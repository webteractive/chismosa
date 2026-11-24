<?php

namespace Database\Factories;

use App\Models\RelayKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RelayKey>
 */
class RelayKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->password(32, 32),
        ];
    }
}
