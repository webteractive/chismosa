<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayKey;
use App\Mcp\Servers\ChismosaServer;
use App\Mcp\Tools\RotateRelayKeyTool;
use App\Mcp\Tools\GetCredentialsLinkTool;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('get-credentials-link', function () {
    it('returns a signed link that shows the relay credentials to the user who asked', function () {
        $this->freezeTime();
        RelayKey::factory()->create(['key' => 'the-relay-key']);
        $user = User::factory()->create();
        $relay = Relay::factory()->create(['webhook_url' => 'https://chat.example.com/hook/secret-token']);
        $link = null;

        ChismosaServer::actingAs($user)
            ->tool(GetCredentialsLinkTool::class, ['relay_id' => $relay->id])
            ->assertOk()
            ->assertDontSee(['the-relay-key', 'secret-token'])
            ->assertStructuredContent(function (AssertableJson $json) use (&$link) {
                $json->where('expires_at', now()->addMinutes(10)->toIso8601String())
                    ->where('url', function (string $url) use (&$link) {
                        $link = $url;

                        return str_contains($url, 'signature=');
                    });
            });

        $this->actingAs($user)
            ->get($link)
            ->assertOk()
            ->assertSee('the-relay-key')
            ->assertSee('https://chat.example.com/hook/secret-token');
    });

    it('returns an error when the relay does not exist', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(GetCredentialsLinkTool::class, ['relay_id' => 999])
            ->assertHasErrors(['Relay 999 was not found.']);
    });
});

describe('rotate-relay-key', function () {
    it('replaces the relay key and returns a link instead of the key', function () {
        RelayKey::factory()->create(['key' => 'old-relay-key']);
        Relay::factory()->count(2)->create();
        RelayKey::current();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(RotateRelayKeyTool::class)
            ->assertOk()
            ->assertDontSee(RelayKey::query()->sole()->key)
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('rotated', true)
                ->where('relays_affected', 2)
                ->has('credentials_link.url')
                ->has('credentials_link.expires_at'));

        expect(RelayKey::query()->sole()->key)->not->toBe('old-relay-key')
            ->and(RelayKey::current())->toBe(RelayKey::query()->sole()->key);
    });

    it('creates the relay key when none exists yet', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(RotateRelayKeyTool::class)
            ->assertOk();

        expect(RelayKey::query()->sole()->key)->toHaveLength(64);
    });
});
