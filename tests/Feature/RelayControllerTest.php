<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayKey;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    RelayKey::factory()->create(['key' => 'test-secret-key']);
});

test('relay endpoint requires valid relay id', function () {
    $response = $this->postJson('/relay/999/invalid-key');

    $response->assertStatus(404);
});

test('relay endpoint requires valid secret key', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'secret' => Hash::make('test-secret-key'),
    ]);

    $response = $this->postJson("/relay/{$relay->id}/wrong-key");

    $response->assertStatus(404);
});

test('relay endpoint accepts valid request', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'secret' => Hash::make('test-secret-key'),
    ]);

    $payload = [
        'status' => 'success',
        'commit_message' => 'Test commit',
        'commit_hash' => 'abc123',
    ];

    $response = $this->postJson("/relay/{$relay->id}/test-secret-key", $payload);

    $response->assertStatus(200);

    // Verify log was created
    $this->assertDatabaseHas('relay_logs', [
        'relay_id' => $relay->id,
    ]);
});

test('relay endpoint works with get request', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'secret' => Hash::make('test-secret-key'),
    ]);

    $response = $this->get("/relay/{$relay->id}/test-secret-key");

    $response->assertStatus(200);
});

test('relay endpoint logs payload', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'secret' => Hash::make('test-secret-key'),
    ]);

    $payload = [
        'status' => 'success',
        'message' => 'Test message',
    ];

    $this->postJson("/relay/{$relay->id}/test-secret-key", $payload);

    $this->assertDatabaseHas('relay_logs', [
        'relay_id' => $relay->id,
    ]);

    $log = $relay->logs()->first();
    expect($log)->not->toBeNull()
        ->and($log->payload['status'])->toBe('success')
        ->and($log->payload['message'])->toBe('Test message');
});
