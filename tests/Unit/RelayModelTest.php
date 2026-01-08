<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayKey;
use App\Models\RelayLog;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    RelayKey::factory()->create(['key' => 'test-secret-key']);
});

test('relay belongs to user', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    expect($relay->user)
        ->toBeInstanceOf(User::class)
        ->and($relay->user->id)->toBe($user->id);
});

test('relay has many logs', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    RelayLog::factory()->count(3)->create(['relay_id' => $relay->id]);

    expect($relay->logs)->toHaveCount(3)
        ->and($relay->logs->first())->toBeInstanceOf(RelayLog::class);
});

test('relay endpoint accessor', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    $endpoint = $relay->endpoint;

    expect($endpoint)
        ->toContain("/relay/{$relay->id}/")
        ->toContain('test-secret-key');
});

test('relay fillable attributes', function () {
    $user = User::factory()->create();
    $relay = Relay::create([
        'name' => 'Test Relay',
        'type' => 'forge',
        'description' => 'Test Description',
        'webhook_type' => 'google_chat',
        'webhook_url' => 'https://example.com/webhook',
        'secret' => Hash::make('secret'),
        'status' => 1,
        'user_id' => $user->id,
    ]);

    expect($relay->name)->toBe('Test Relay')
        ->and($relay->type)->toBe('forge')
        ->and($relay->description)->toBe('Test Description')
        ->and($relay->webhook_type)->toBe('google_chat')
        ->and($relay->webhook_url)->toBe('https://example.com/webhook')
        ->and($relay->status)->toBe(1)
        ->and($relay->user_id)->toBe($user->id);
});
