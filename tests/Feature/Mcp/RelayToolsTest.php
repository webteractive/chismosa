<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayKey;
use App\Models\RelayLog;
use App\Mcp\Tools\GetRelayTool;
use App\Mcp\Tools\TestRelayTool;
use App\Mcp\Tools\ListRelaysTool;
use App\Mcp\Tools\CreateRelayTool;
use App\Mcp\Tools\DeleteRelayTool;
use App\Mcp\Tools\UpdateRelayTool;
use App\Mcp\Servers\ChismosaServer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('list-relays', function () {
    it('returns relays without their endpoint, relay key or webhook URL', function () {
        RelayKey::factory()->create(['key' => 'the-relay-key']);
        $relay = Relay::factory()->create([
            'name' => 'Deploys',
            'webhook_url' => 'https://chat.googleapis.com/v1/spaces/AAA/messages?key=secret-token',
        ]);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(ListRelaysTool::class)
            ->assertOk()
            ->assertSee('Deploys')
            ->assertDontSee(['the-relay-key', 'secret-token', 'messages?key'])
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('total', 1)
                ->where('data.0.id', $relay->id)
                ->where('data.0.webhook_host', 'chat.googleapis.com')
                ->missing('data.0.webhook_url')
                ->missing('data.0.endpoint')
                ->etc());
    });

    it('returns only inactive relays when active is false', function () {
        Relay::factory()->active()->create();
        $inactive = Relay::factory()->inactive()->create();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(ListRelaysTool::class, ['active' => false])
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('total', 1)
                ->where('data.0.id', $inactive->id)
                ->etc());
    });

    it('returns only relays whose name matches the search', function () {
        $match = Relay::factory()->create(['name' => 'Production deploys']);
        Relay::factory()->create(['name' => 'Staging alerts']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(ListRelaysTool::class, ['search' => 'deploy'])
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('total', 1)
                ->where('data.0.id', $match->id)
                ->etc());
    });
});

describe('get-relay', function () {
    it('returns the relay with its log count and no credentials', function () {
        $relay = Relay::factory()->create(['webhook_url' => 'https://chat.example.com/hook/secret-token']);
        RelayLog::factory()->count(2)->create(['relay_id' => $relay->id]);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(GetRelayTool::class, ['id' => $relay->id])
            ->assertOk()
            ->assertDontSee('secret-token')
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('id', $relay->id)
                ->where('logs_count', 2)
                ->etc());
    });

    it('returns an error when the relay does not exist', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(GetRelayTool::class, ['id' => 999])
            ->assertHasErrors(['Relay 999 was not found.']);
    });
});

describe('create-relay', function () {
    it('creates an active relay owned by the signed-in user', function () {
        $user = User::factory()->create();

        ChismosaServer::actingAs($user)
            ->tool(CreateRelayTool::class, [
                'name' => 'Deploys',
                'type' => 'forge',
                'webhook_type' => 'google_chat',
                'webhook_url' => 'https://chat.example.com/hook/secret-token',
            ])
            ->assertOk()
            ->assertDontSee('secret-token');

        $this->assertDatabaseHas(Relay::class, [
            'name' => 'Deploys',
            'type' => 'forge',
            'webhook_type' => 'google_chat',
            'webhook_url' => 'https://chat.example.com/hook/secret-token',
            'status' => 1,
            'user_id' => $user->id,
        ]);
    });

    it('returns a link to the new relay\'s credentials instead of the credentials', function () {
        RelayKey::factory()->create(['key' => 'the-relay-key']);
        $user = User::factory()->create();
        $link = null;

        ChismosaServer::actingAs($user)
            ->tool(CreateRelayTool::class, [
                'name' => 'Deploys',
                'type' => 'forge',
                'webhook_type' => 'google_chat',
                'webhook_url' => 'https://chat.example.com/hook/secret-token',
            ])
            ->assertOk()
            ->assertDontSee(['the-relay-key', 'secret-token'])
            ->assertStructuredContent(function (AssertableJson $json) use (&$link) {
                $json->has('credentials_link.expires_at')
                    ->where('credentials_link.url', function (string $url) use (&$link) {
                        $link = $url;

                        return str_contains($url, 'signature=');
                    })
                    ->etc();
            });

        $this->actingAs($user)
            ->get($link)
            ->assertOk()
            ->assertSee('the-relay-key')
            ->assertSee('https://chat.example.com/hook/secret-token');
    });

    it('rejects a service type that is not supported', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(CreateRelayTool::class, [
                'name' => 'Deploys',
                'type' => 'github',
                'webhook_type' => 'google_chat',
                'webhook_url' => 'https://chat.example.com/hook',
            ])
            ->assertHasErrors(['The selected type is invalid.']);

        $this->assertDatabaseCount(Relay::class, 0);
    });

    it('rejects a webhook URL that is not a URL', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(CreateRelayTool::class, [
                'name' => 'Deploys',
                'type' => 'forge',
                'webhook_type' => 'google_chat',
                'webhook_url' => 'not a url',
            ])
            ->assertHasErrors(['The webhook url field must be a valid URL.']);

        $this->assertDatabaseCount(Relay::class, 0);
    });
});

describe('update-relay', function () {
    it('deactivates a relay and leaves its other fields alone', function () {
        $relay = Relay::factory()->active()->create(['name' => 'Deploys']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(UpdateRelayTool::class, ['id' => $relay->id, 'active' => false])
            ->assertOk()
            ->assertStructuredContent(fn (AssertableJson $json) => $json->where('active', false)->etc());

        expect($relay->refresh())
            ->status->toBe(0)
            ->name->toBe('Deploys');
    });

    it('rejects an owner that does not exist', function () {
        $relay = Relay::factory()->create();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(UpdateRelayTool::class, ['id' => $relay->id, 'user_id' => 999])
            ->assertHasErrors(['The selected user id is invalid.']);
    });

    it('returns an error when the relay does not exist', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(UpdateRelayTool::class, ['id' => 999, 'name' => 'Renamed'])
            ->assertHasErrors(['Relay 999 was not found.']);
    });
});

describe('delete-relay', function () {
    it('deletes the relay and its logs but not the logs of other relays', function () {
        $relay = Relay::factory()->create();
        RelayLog::factory()->count(2)->create(['relay_id' => $relay->id]);
        $otherLog = RelayLog::factory()->create();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(DeleteRelayTool::class, ['id' => $relay->id])
            ->assertOk()
            ->assertSee('2 logged payloads');

        $this->assertModelMissing($relay);
        $this->assertDatabaseMissing(RelayLog::class, ['relay_id' => $relay->id]);
        $this->assertModelExists($otherLog);
    });

    it('returns an error when the relay does not exist', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(DeleteRelayTool::class, ['id' => 999])
            ->assertHasErrors(['Relay 999 was not found.']);
    });
});

describe('test-relay', function () {
    it('sends a sample message to the destination without logging it', function () {
        Http::fake();

        $relay = Relay::factory()->create(['type' => 'forge', 'webhook_url' => 'https://chat.example.com/hook']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(TestRelayTool::class, ['id' => $relay->id])
            ->assertOk()
            ->assertSee('it was accepted');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://chat.example.com/hook');

        expect(RelayLog::count())->toBe(0);
    });

    it('reports a rejection without revealing the webhook URL', function () {
        Http::fake(['*' => Http::response('nope', 500)]);

        $relay = Relay::factory()->create([
            'type' => 'forge',
            'webhook_url' => 'https://chat.googleapis.com/v1/spaces/AAA/messages?key=secret-token',
        ]);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(TestRelayTool::class, ['id' => $relay->id])
            ->assertHasErrors(["The test message for relay {$relay->id} failed. The destination rejected the message with HTTP 500."])
            ->assertDontSee('secret-token');
    });

    it('reports an unreachable destination without revealing the webhook URL', function () {
        Http::fake(fn () => throw new ConnectionException('cURL error 6 for https://chat.googleapis.com/messages?key=secret-token'));

        $relay = Relay::factory()->create(['type' => 'forge']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(TestRelayTool::class, ['id' => $relay->id])
            ->assertHasErrors(["The test message for relay {$relay->id} failed. The destination could not be reached."])
            ->assertDontSee('secret-token');
    });

    it('returns an error for a relay type without an outgoing message', function () {
        Http::fake();

        $relay = Relay::factory()->create(['type' => 'google_chat']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(TestRelayTool::class, ['id' => $relay->id])
            ->assertHasErrors(["Relay {$relay->id} receives google_chat webhooks, which have no outgoing message to test."]);

        Http::assertNothingSent();
    });
});
