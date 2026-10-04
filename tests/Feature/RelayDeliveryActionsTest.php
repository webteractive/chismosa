<?php

use App\Models\User;
use App\Models\Relay;
use Livewire\Livewire;
use App\Models\RelayLog;
use App\Jobs\SendRelayMessage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\Relays\Pages\ManageRelays;
use App\Filament\Resources\RelayLogs\Pages\ManageRelayLogs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('resend', function () {
    it('queues the logged payload again without logging it twice', function () {
        Queue::fake();

        $log = RelayLog::factory()->create([
            'relay_id' => Relay::factory()->create(['type' => 'forge'])->id,
            'payload' => ['status' => 'success', 'commit_message' => 'Ship it'],
        ]);

        Livewire::test(ManageRelayLogs::class)
            ->callAction(TestAction::make('resend')->table($log))
            ->assertNotified('Payload queued for delivery');

        Queue::assertPushed(SendRelayMessage::class, fn (SendRelayMessage $job) => $job->relay->is($log->relay)
            && $job->payload === ['status' => 'success', 'commit_message' => 'Ship it']);

        expect(RelayLog::count())->toBe(1);
    });

    it('queues nothing for a relay type without an outgoing message', function () {
        Queue::fake();

        $log = RelayLog::factory()->create([
            'relay_id' => Relay::factory()->create(['type' => 'google_chat'])->id,
        ]);

        Livewire::test(ManageRelayLogs::class)
            ->callAction(TestAction::make('resend')->table($log))
            ->assertNotified('Nothing to resend');

        Queue::assertNothingPushed();
    });
});

describe('test message', function () {
    it('sends a sample message straight to the destination without logging it', function () {
        Http::fake();

        $relay = Relay::factory()->create(['type' => 'forge', 'webhook_url' => 'https://chat.example.com/hook']);

        Livewire::test(ManageRelays::class)
            ->callAction(TestAction::make('test')->table($relay))
            ->assertNotified('Test message delivered');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://chat.example.com/hook'
            && str_contains($request->body(), 'Test message from Chismosa'));

        expect(RelayLog::count())->toBe(0);
    });

    it('reports the status when the destination rejects the message', function () {
        Http::fake(['*' => Http::response('nope', 404)]);

        $relay = Relay::factory()->create(['type' => 'forge']);

        Livewire::test(ManageRelays::class)
            ->callAction(TestAction::make('test')->table($relay))
            ->assertNotified('Test message failed');
    });
});
