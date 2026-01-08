<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;
use Illuminate\Support\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('purge old relay logs command deletes old logs', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    // Create old log (more than a month ago)
    $oldLog = RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subMonths(2),
    ]);

    // Create recent log (less than a month ago)
    $recentLog = RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subDays(10),
    ]);

    $this->artisan('relay:purge_old_logs')
        ->expectsOutput(__(':count old relay logs has been purged.', ['count' => 1]))
        ->assertSuccessful();

    // Old log should be deleted
    $this->assertDatabaseMissing('relay_logs', ['id' => $oldLog->id]);

    // Recent log should still exist
    $this->assertDatabaseHas('relay_logs', ['id' => $recentLog->id]);
});

test('purge old relay logs command handles no old logs', function () {
    $user = User::factory()->create();
    $relay = Relay::factory()->create(['user_id' => $user->id]);

    // Create only recent logs
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

    // Create multiple old logs
    RelayLog::factory()->count(5)->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subMonths(2),
    ]);

    // Create recent log
    RelayLog::factory()->create([
        'relay_id' => $relay->id,
        'created_at' => Carbon::now()->subDays(10),
    ]);

    $this->artisan('relay:purge_old_logs')
        ->expectsOutput(__(':count old relay logs has been purged.', ['count' => 5]))
        ->assertSuccessful();

    // Should only have 1 log remaining (the recent one)
    expect(RelayLog::count())->toBe(1);
});
