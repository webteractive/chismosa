<?php

use App\Models\Relay;
use App\Jobs\SendRelayMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('job is queueable and retries with backoff', function () {
    $job = new SendRelayMessage(Relay::factory()->create(), []);

    $reflection = new ReflectionClass($job);
    $attribute = fn (string $name) => $reflection->getAttributes($name)[0]->getArguments()[0];

    expect($job)->toBeInstanceOf(ShouldQueue::class)
        ->and($attribute(Tries::class))->toBe(3)
        ->and($attribute(Backoff::class))->toBe([10, 60]);
});

test('job posts the forge message to the relay webhook', function () {
    $relay = Relay::factory()->create([
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    Http::fake(['example.com/*' => Http::response(['ok' => true])]);

    (new SendRelayMessage($relay, [
        'status' => 'success',
        'commit_message' => 'Ship it',
        'server' => ['name' => 'web-1'],
    ]))->handle();

    Http::assertSent(function ($request) {
        $widgets = $request['cards'][0]['sections'][0]['widgets'][0];

        return $request->url() === 'https://example.com/webhook'
            && str_contains($widgets[0]['textParagraph']['text'], 'A new update has been deployed')
            && $widgets[1]['keyValue']['content'] === 'Ship it'
            && $widgets[4]['keyValue']['content'] === 'web-1';
    });
});

test('job reports a failed deployment when status is not success', function () {
    $relay = Relay::factory()->create([
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    Http::fake(['example.com/*' => Http::response(['ok' => true])]);

    (new SendRelayMessage($relay, []))->handle();

    Http::assertSent(fn ($request) => str_contains(
        $request['cards'][0]['sections'][0]['widgets'][0][0]['textParagraph']['text'],
        'Deployment failed!'
    ));
});

test('job throws when the destination rejects the message so it can be retried', function (int $status) {
    $relay = Relay::factory()->create([
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    Http::fake(['example.com/*' => Http::response([], $status)]);

    (new SendRelayMessage($relay, ['status' => 'success']))->handle();
})->with([400, 429, 500])->throws(RequestException::class);

test('job throws when the destination is unreachable', function () {
    $relay = Relay::factory()->create([
        'type' => 'forge',
        'webhook_url' => 'https://example.com/webhook',
    ]);

    Http::fake(['example.com/*' => Http::failedConnection()]);

    (new SendRelayMessage($relay, ['status' => 'success']))->handle();
})->throws(ConnectionException::class);

test('job sends nothing for a relay type without a message', function () {
    $relay = Relay::factory()->create(['type' => 'google_chat']);

    Http::fake();

    (new SendRelayMessage($relay, ['status' => 'success']))->handle();

    Http::assertNothingSent();
});
