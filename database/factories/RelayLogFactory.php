<?php

namespace Database\Factories;

use App\Models\Relay;
use App\Models\RelayLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelayLogFactory extends Factory
{
    protected $model = RelayLog::class;

    public function definition(): array
    {
        return [
            'relay_id' => Relay::factory(),
            'payload' => [
                'status' => $this->faker->randomElement(['success', 'failed']),
                'message' => $this->faker->sentence(),
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    public function withPayload(array $payload): static
    {
        return $this->state(fn (array $attributes) => [
            'payload' => $payload,
        ]);
    }
}
