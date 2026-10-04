<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function registerMcpClient(): string
{
    return app(ClientRepository::class)
        ->createAuthorizationCodeGrantClient('MCP Client', ['https://claude.ai/api/mcp/auth_callback'], confidential: false)
        ->id;
}

it('purges registered clients that were never authorized after a day', function () {
    $unused = registerMcpClient();
    $authorized = registerMcpClient();

    DB::table('oauth_access_tokens')->insert([
        'id' => Str::random(80),
        'user_id' => User::factory()->create()->id,
        'client_id' => $authorized,
        'revoked' => false,
    ]);

    $this->travel(2)->days();

    $fresh = registerMcpClient();

    $this->artisan('mcp:purge_unused_clients')
        ->expectsOutput(__(':count unused OAuth clients has been purged.', ['count' => 1]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('oauth_clients', ['id' => $unused]);
    $this->assertDatabaseHas('oauth_clients', ['id' => $authorized]);
    $this->assertDatabaseHas('oauth_clients', ['id' => $fresh]);
});

it('keeps clients that did not come from open registration', function () {
    $clients = app(ClientRepository::class);

    $confidential = $clients->createAuthorizationCodeGrantClient('Confidential', ['https://claude.ai/callback'])->id;
    $owned = $clients->createAuthorizationCodeGrantClient('Owned', ['https://claude.ai/callback'], false, User::factory()->create())->id;
    $password = $clients->createPasswordGrantClient('Password')->id;

    $this->travel(2)->days();

    $this->artisan('mcp:purge_unused_clients')->assertSuccessful();

    $this->assertDatabaseHas('oauth_clients', ['id' => $confidential]);
    $this->assertDatabaseHas('oauth_clients', ['id' => $owned]);
    $this->assertDatabaseHas('oauth_clients', ['id' => $password]);
});
