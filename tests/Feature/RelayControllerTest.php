<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayKey;
use App\Jobs\SendRelayMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        'failing.example/*' => Http::response([], 500),
        '*' => Http::response(),
    ]);
    RelayKey::factory()->create(['key' => 'test-secret-key']);
});

test('relay endpoint requires valid relay id', function () {
    $response = $this->postJson('/relay/999/invalid-key');

    $response->assertNotFound();
});

test('relay endpoint requires the current relay key', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->postJson("/relay/{$relay->id}/wrong-key");

    $response->assertNotFound();
});

test('relay endpoint accepts valid request', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
    ]);

    $payload = [
        'status' => 'success',
        'commit_message' => 'Test commit',
        'commit_hash' => 'abc123',
    ];

    $response = $this->postJson("/relay/{$relay->id}/test-secret-key", $payload);

    $response->assertOk();

    $this->assertDatabaseHas('relay_logs', [
        'relay_id' => $relay->id,
    ]);
});

test('relay endpoint works with get request', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->get("/relay/{$relay->id}/test-secret-key");

    $response->assertOk();
});

test('relay endpoint logs payload', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create([
        'user_id' => $user->id,
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

test('relay endpoint queues the message and responds without calling the webhook', function () {
    Queue::fake();

    $relay = Relay::factory()->create([
        'type' => 'forge',
    ]);

    $payload = ['status' => 'success', 'commit_message' => 'Test commit'];

    $this->postJson("/relay/{$relay->id}/test-secret-key", $payload)
        ->assertOk();

    Queue::assertPushed(
        SendRelayMessage::class,
        fn (SendRelayMessage $job) => $job->relay->is($relay) && $job->payload === $payload
    );
    Http::assertNothingSent();
    $this->assertDatabaseHas('relay_logs', ['relay_id' => $relay->id]);
});

test('relay endpoint does not queue anything for an unauthorized request', function () {
    Queue::fake();

    $relay = Relay::factory()->create([
        'type' => 'forge',
    ]);

    $this->postJson("/relay/{$relay->id}/wrong-key", ['status' => 'success'])
        ->assertNotFound();

    Queue::assertNothingPushed();
    $this->assertDatabaseMissing('relay_logs', ['relay_id' => $relay->id]);
});

test('relay endpoint still logs the payload for a type without a message', function () {
    Queue::fake();

    $relay = Relay::factory()->create([
        'type' => 'google_chat',
    ]);

    $this->postJson("/relay/{$relay->id}/test-secret-key", ['status' => 'success'])
        ->assertOk();

    Queue::assertNothingPushed();
    $this->assertDatabaseHas('relay_logs', ['relay_id' => $relay->id]);
});

test('message waits on the database queue until a worker delivers it', function () {
    config(['queue.default' => 'database']);

    $relay = Relay::factory()->create([
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    $this->postJson("/relay/{$relay->id}/test-secret-key", ['status' => 'success'])
        ->assertOk();

    expect(DB::table('jobs')->count())->toBe(1);
    Http::assertNothingSent();

    $this->artisan('queue:work', ['--once' => true])->assertSuccessful();

    Http::assertSent(fn ($request) => $request->url() === 'https://example.com/webhook');
    expect(DB::table('jobs')->count())->toBe(0)
        ->and(DB::table('failed_jobs')->count())->toBe(0);
});

test('message is released for another attempt when the webhook fails', function () {
    config(['queue.default' => 'database']);

    $relay = Relay::factory()->create([
        'type' => 'forge',
        'webhook_url' => 'https://failing.example/webhook',
    ]);

    $this->postJson("/relay/{$relay->id}/test-secret-key", ['status' => 'success'])
        ->assertOk();

    $this->artisan('queue:work', ['--once' => true]);

    expect(DB::table('jobs')->count())->toBe(1)
        ->and(DB::table('jobs')->value('attempts'))->toBe(1)
        ->and(DB::table('failed_jobs')->count())->toBe(0);
});

test('inactive relay is rejected without logging or queueing', function () {
    Queue::fake();

    $relay = Relay::factory()->inactive()->create([
        'type' => 'forge',
    ]);

    $this->postJson("/relay/{$relay->id}/test-secret-key", ['status' => 'success'])
        ->assertNotFound();

    Queue::assertNothingPushed();
    $this->assertDatabaseMissing('relay_logs', ['relay_id' => $relay->id]);
});

test('reactivated relay accepts requests again', function () {
    Queue::fake();

    $relay = Relay::factory()->inactive()->create([
        'type' => 'forge',
    ]);

    $relay->update(['status' => 1]);

    $this->postJson("/relay/{$relay->id}/test-secret-key", ['status' => 'success'])
        ->assertOk();

    Queue::assertPushed(SendRelayMessage::class);
});

test('existing relays keep working after the relay key is rotated', function () {
    Queue::fake();

    $relay = Relay::factory()->create(['type' => 'forge']);

    RelayKey::query()->first()->update(['key' => 'rotated-key']);
    Cache::forget('relay-key-current');

    $this->postJson("/relay/{$relay->id}/rotated-key", ['status' => 'success'])
        ->assertOk();

    $this->postJson("/relay/{$relay->id}/test-secret-key", ['status' => 'success'])
        ->assertNotFound();

    Queue::assertPushed(SendRelayMessage::class, 1);
});

test('relay endpoint rejects every request when no relay key is set', function () {
    $relay = Relay::factory()->create(['type' => 'forge']);

    RelayKey::query()->delete();
    Cache::forget('relay-key-current');

    $this->postJson("/relay/{$relay->id}/test-secret-key")->assertNotFound();
    $this->postJson("/relay/{$relay->id}/null")->assertNotFound();
});
