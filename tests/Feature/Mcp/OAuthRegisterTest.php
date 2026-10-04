<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a client using an allowed private-use scheme', function (string $uri) {
    $this->postJson('/oauth/register', ['redirect_uris' => [$uri]])
        ->assertCreated()
        ->assertJsonPath('redirect_uris.0', $uri);
})->with([
    'claude desktop' => ['claude://claude.ai/oauth/callback'],
    'cursor' => ['cursor://anysphere.cursor-mcp/oauth/callback'],
    'vscode' => ['vscode://anysphere.cursor/oauth/callback'],
]);

it('registers a cli client redirecting back to loopback', function (string $uri) {
    $this->postJson('/oauth/register', ['redirect_uris' => [$uri]])->assertCreated();
})->with([
    'localhost' => ['http://localhost:1455/callback'],
    'loopback ip' => ['http://127.0.0.1:1455/callback'],
]);

it('registers a hosted client on a provider origin', function (string $uri) {
    $this->postJson('/oauth/register', ['redirect_uris' => [$uri]])
        ->assertCreated()
        ->assertJsonPath('redirect_uris.0', $uri);
})->with([
    'claude.ai' => ['https://claude.ai/api/mcp/auth_callback'],
    'claude.com' => ['https://claude.com/api/mcp/auth_callback'],
]);

it('registers Cursor, which sends a scheme, loopback and hosted origin together', function () {
    $this->postJson('/oauth/register', [
        'client_name' => 'Cursor',
        'redirect_uris' => [
            'cursor://anysphere.cursor-mcp/oauth/callback',
            'http://localhost:8787/callback',
            'https://www.cursor.com/agents/mcp/oauth/callback',
        ],
    ])->assertCreated();
});

it('returns 400 for a redirect to a host that is not allowed', function (string $uri) {
    $this->postJson('/oauth/register', ['redirect_uris' => [$uri]])
        ->assertStatus(400)
        ->assertJsonPath('error', 'invalid_redirect_uri');
})->with([
    'attacker host' => ['https://evil.tld/callback'],
    'lookalike subdomain' => ['https://localhost.evil.tld/callback'],
    'lookalike of an allowed origin' => ['https://claude.ai.evil.tld/callback'],
    'lookalike of a cursor origin' => ['https://www.cursor.com.evil.tld/callback'],
    'localhost as a path, not a host' => ['https://evil.tld/http://localhost/callback'],
]);

it('throttles registrations from one client to 10 a minute', function () {
    $payload = ['redirect_uris' => ['https://claude.ai/api/mcp/auth_callback']];

    foreach (range(1, 10) as $attempt) {
        $this->postJson('/oauth/register', $payload)->assertCreated();
    }

    $this->postJson('/oauth/register', $payload)->assertTooManyRequests();

    $this->assertDatabaseCount('oauth_clients', 10);
});

it('returns 400 for a whole payload when a single entry is not allowed', function () {
    $this->postJson('/oauth/register', ['redirect_uris' => [
        'https://claude.ai/api/mcp/auth_callback',
        'http://localhost:8787/callback',
        'https://evil.tld/callback',
    ]])->assertStatus(400)->assertJsonPath('error', 'invalid_redirect_uri');
});
