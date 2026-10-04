<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('horizon is served beneath the admin path', function () {
    expect(config('horizon.path'))->toBe(config('chismosa.admin_path').'/horizon')
        ->and(route('horizon.index', absolute: false))->toBe('/'.config('chismosa.admin_path').'/horizon');
});

test('guests cannot view horizon', function () {
    $this->get(route('horizon.index'))->assertForbidden();
    $this->getJson(route('horizon.stats.index'))->assertForbidden();
});

test('admin users can view horizon', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('horizon.index'))
        ->assertOk();
});

test('horizon is not exposed at the default path', function () {
    $this->get('/horizon')->assertDontSee('Horizon');
});

test('horizon and redis keys are namespaced to this app regardless of the app name', function () {
    expect(config('horizon.prefix'))->toBe('chismosa_horizon:')
        ->and(config('horizon.name'))->toBe('Chismosa')
        ->and(config('database.redis.options.prefix'))->toBe('chismosa-database-')
        ->and(config('horizon.prefix'))->not->toContain(str(config('app.name'))->slug('_')->toString().'_horizon');
});

test('horizon supervises the redis queue in every configured environment', function (string $environment) {
    $supervisors = config("horizon.environments.{$environment}");

    expect($supervisors)->toHaveKey('chismosa-supervisor')
        ->and(config('horizon.defaults.chismosa-supervisor.connection'))->toBe('redis')
        ->and(config('horizon.defaults.chismosa-supervisor.queue'))->toBe(['default'])
        ->and(config('horizon.defaults.chismosa-supervisor.timeout'))
        ->toBeLessThan(config('queue.connections.redis.retry_after'));
})->with(['production', 'local']);

test('horizon metrics snapshots are scheduled', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('horizon:snapshot')
        ->assertSuccessful();
});
