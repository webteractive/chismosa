<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;
use App\Support\Relayer;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('relayer can be created with make', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    $relayer = Relayer::make($relay);

    expect($relayer)->toBeInstanceOf(Relayer::class);
});

test('relayer logs payload', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    $payload = ['status' => 'success', 'message' => 'Test'];

    Relayer::make($relay)
        ->withPayload($payload)
        ->log();

    $this->assertDatabaseHas('relay_logs', [
        'relay_id' => $relay->id,
    ]);

    $log = RelayLog::where('relay_id', $relay->id)->first();
    expect($log)->not->toBeNull()
        ->and($log->payload['status'])->toBe('success');
});

test('relayer sends notification for forge type', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    $payload = [
        'status' => 'success',
        'commit_message' => 'Test commit',
    ];

    Http::fake(function ($request) {
        return Http::response(['success' => true], 200);
    });

    Relayer::make($relay)
        ->withPayload($payload)
        ->notify();

    Http::assertSent(function ($request) use ($relay) {
        return $request->url() === $relay->webhook_url;
    });
});

test('relayer does not send notification for unknown type', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'type' => 'unknown_type',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    Http::fake();

    Relayer::make($relay)
        ->withPayload(['status' => 'success'])
        ->notify();

    Http::assertNothingSent();
});
