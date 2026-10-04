<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayKey;
use Filament\Facades\Filament;
use App\Support\CredentialReveal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects a guest to the admin sign-in', function () {
    $link = CredentialReveal::create(User::factory()->create());

    $this->get($link['url'])->assertRedirect(Filament::getLoginUrl());
});

it('shows the relay key and every endpoint, but no webhook URL, when no relay is given', function () {
    RelayKey::factory()->create(['key' => 'the-relay-key']);
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['webhook_url' => 'https://chat.example.com/hook/secret-token']);
    $link = CredentialReveal::create($user);

    $this->actingAs($user)
        ->get($link['url'])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee(route('relay', ['id' => $relay->id, 'key' => 'the-relay-key']))
        ->assertDontSee('secret-token');
});

it('shows only the given relay when one is given', function () {
    RelayKey::factory()->create(['key' => 'the-relay-key']);
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['name' => 'Deploys']);
    Relay::factory()->create(['name' => 'Staging alerts']);
    $link = CredentialReveal::create($user, $relay);

    $this->actingAs($user)
        ->get($link['url'])
        ->assertOk()
        ->assertSee('Deploys')
        ->assertSee($relay->webhook_url)
        ->assertDontSee('Staging alerts');
});

it('returns 404 to a signed-in user who did not ask for the link', function () {
    $link = CredentialReveal::create(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->get($link['url'])
        ->assertNotFound();
});

it('returns 403 when the signature has been tampered with', function () {
    $user = User::factory()->create();
    $link = CredentialReveal::create($user);

    $this->actingAs($user)
        ->get($link['url'].'tampered')
        ->assertForbidden();
});

it('returns 403 once the link has expired', function () {
    $user = User::factory()->create();
    $link = CredentialReveal::create($user);

    $this->travel(CredentialReveal::LIFETIME_IN_MINUTES + 1)->minutes();

    $this->actingAs($user)
        ->get($link['url'])
        ->assertForbidden();
});

it('returns 404 after the page has been closed', function () {
    $user = User::factory()->create();
    $link = CredentialReveal::create($user);
    $token = basename(parse_url($link['url'], PHP_URL_PATH));

    $this->actingAs($user)
        ->delete(route('credentials.destroy', ['reveal' => $token]))
        ->assertNoContent();

    $this->actingAs($user)
        ->get($link['url'])
        ->assertNotFound();
});

it('does not let another user discard the link', function () {
    $owner = User::factory()->create();
    $link = CredentialReveal::create($owner);
    $token = basename(parse_url($link['url'], PHP_URL_PATH));

    $this->actingAs(User::factory()->create())
        ->delete(route('credentials.destroy', ['reveal' => $token]))
        ->assertNoContent();

    $this->actingAs($owner)
        ->get($link['url'])
        ->assertOk();
});
