<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('purge old relay logs command deletes old logs', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    $oldLog = RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subMonths(2),
    ]);

    $recentLog = RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subDays(10),
    ]);

    $this->artisan('relay:purge_old_logs')
        ->expectsOutput(__(':count old relay logs has been purged.', ['count' => 1]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('relay_logs', ['id' => $oldLog->id]);

    $this->assertDatabaseHas('relay_logs', ['id' => $recentLog->id]);
});

test('purge old relay logs command handles no old logs', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subDays(10),
    ]);

    $this->artisan('relay:purge_old_logs')
        ->expectsOutput(__(':count old relay logs has been purged.', ['count' => 0]))
        ->assertSuccessful();
});

test('purge old relay logs command deletes multiple old logs', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    RelayLog::factory()->count(5)->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subMonths(2),
    ]);

    RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subDays(10),
    ]);

    $this->artisan('relay:purge_old_logs')
        ->expectsOutput(__(':count old relay logs has been purged.', ['count' => 5]))
        ->assertSuccessful();

    expect(RelayLog::count())->toBe(1);
});
