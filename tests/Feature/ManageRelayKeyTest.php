<?php

use App\Models\User;
use App\Models\RelayKey;

use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can create a new relay key', function () {
    $data = ['key' => 'new-secret-key'];

    $record = RelayKey::query()->first();

    if (! $record) {
        $record = new RelayKey;
    }

    $record->fill($data);
    $record->save();

    assertDatabaseHas(RelayKey::class, [
        'key' => 'new-secret-key',
    ]);
});

it('can update an existing relay key', function () {
    $relayKey = RelayKey::factory()->create(['key' => 'old-key']);

    $data = ['key' => 'updated-key'];

    $record = RelayKey::query()->first();
    $record->fill($data);
    $record->save();

    expect($relayKey->refresh()->key)->toBe('updated-key');
});

it('only allows one relay key to exist', function () {
    RelayKey::factory()->create(['key' => 'first-key']);

    $data = ['key' => 'second-key'];

    $record = RelayKey::query()->first();
    $record->fill($data);
    $record->save();

    expect(RelayKey::count())->toBe(1)
        ->and(RelayKey::first()->key)->toBe('second-key');
});
