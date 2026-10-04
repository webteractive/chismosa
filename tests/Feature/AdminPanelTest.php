<?php

use App\Models\User;
use App\Models\Relay;
use Livewire\Livewire;
use App\Models\RelayLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Resources\Relays\Pages\ManageRelays;
use App\Filament\Resources\RelayLogs\Pages\ManageRelayLogs;

uses(RefreshDatabase::class);

test('guests are redirected to the admin login', function () {
    $this->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

test('guests can see the admin login page', function () {
    $this->get(route('filament.admin.auth.login'))
        ->assertSuccessful();
});

test('authenticated users can load admin pages', function (string $routeName) {
    $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertSuccessful();
})->with([
    'dashboard' => 'filament.admin.pages.dashboard',
    'relays' => 'filament.admin.resources.relays.index',
    'relay logs' => 'filament.admin.resources.relay-logs.index',
    'users' => 'filament.admin.resources.users.index',
]);

test('admin tables list their records', function () {
    $this->actingAs(User::factory()->create());

    $relays = Relay::factory()->count(2)->create();
    $logs = RelayLog::factory()->count(2)->for($relays->first())->create();

    Livewire::test(ManageRelays::class)->assertCanSeeTableRecords($relays);
    Livewire::test(ManageRelayLogs::class)->assertCanSeeTableRecords($logs);
    Livewire::test(ManageUsers::class)->assertCanSeeTableRecords(User::all());
});

test('a relay can be created from the admin panel', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ManageRelays::class)
        ->callAction('create', [
            'name' => 'Production deploys',
            'type' => 'forge',
            'webhook_type' => 'google_chat',
            'webhook_url' => 'https://example.com/webhook',
            'status' => 1,
            'user_id' => $user->id,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('relays', [
        'name' => 'Production deploys',
        'type' => 'forge',
        'status' => 1,
        'user_id' => $user->id,
    ]);
});
