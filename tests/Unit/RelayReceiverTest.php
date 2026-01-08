<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;
use App\Support\RelayReceiver;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('relay receiver handles payload', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    $payload = [
        'status' => 'success',
        'message' => 'Test message',
    ];

    Http::fake();

    $receiver = new RelayReceiver($relay->id);
    $receiver->handle($payload);

    // Verify log was created
    $this->assertDatabaseHas('relay_logs', [
        'relay_id' => $relay->id,
    ]);

    $log = RelayLog::where('relay_id', $relay->id)->first();
    expect($log)->not->toBeNull()
        ->and($log->payload['status'])->toBe('success')
        ->and($log->payload['message'])->toBe('Test message');
});

test('relay receiver sends notification for forge type', function () {
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

    $receiver = new RelayReceiver($relay->id);
    $receiver->handle($payload);

    Http::assertSent(function ($request) use ($relay) {
        return $request->url() === $relay->webhook_url &&
               $request->hasHeader('Content-Type', 'application/json');
    });
});
