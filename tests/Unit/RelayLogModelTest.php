<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('relay log belongs to relay', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);
    $log = RelayLog::factory()->create(['relay_id' => $relay->id]);

    expect($log->relay)
        ->toBeInstanceOf(Relay::class)
        ->and($log->relay->id)->toBe($relay->id);
});

test('relay log payload is casted to array', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    $payload = ['status' => 'success', 'message' => 'Test'];
    $log = RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'payload' => $payload,
    ]);

    expect($log->payload)->toBeArray()
        ->and($log->payload['status'])->toBe('success')
        ->and($log->payload['message'])->toBe('Test');
});

test('relay log can store complex payload', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    $payload = [
        'status' => 'success',
        'commit_message' => 'Test commit',
        'commit_hash' => 'abc123',
        'commit_url' => 'https://github.com/test/commit/abc123',
        'commit_author' => 'John Doe',
        'server' => ['name' => 'Production'],
        'site' => ['name' => 'My Site'],
    ];

    $log = RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'payload' => $payload,
    ]);

    expect($log->payload['status'])->toBe('success')
        ->and($log->payload['server']['name'])->toBe('Production')
        ->and($log->payload['site']['name'])->toBe('My Site');
});
