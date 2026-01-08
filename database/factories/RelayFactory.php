<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Relay;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelayFactory extends Factory
{
    protected $model = Relay::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['forge', 'google_chat']),
            'description' => $this->faker->sentence(),
            'webhook_type' => $this->faker->randomElement(['google_chat', 'forge']),
            'webhook_url' => $this->faker->url(),
            'secret' => Hash::make(\App\Models\RelayKey::current() ?? 'default-secret'),
            'status' => $this->faker->randomElement([0, 1]),
            'user_id' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
