<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Relay;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelayFactory extends Factory
{
    protected $model = Relay::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => 'forge',
            'description' => $this->faker->sentence(),
            'webhook_type' => 'google_chat',
            'webhook_url' => 'https://chat.googleapis.com/v1/spaces/'.Str::random(11).'/messages?key='.Str::random(39).'&token='.Str::random(43),
            'status' => 1,
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
