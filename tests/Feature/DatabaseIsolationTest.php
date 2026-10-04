<?php

use Illuminate\Support\Facades\DB;

test('uses sqlite in memory database', function () {
    $connection = DB::getDefaultConnection();
    expect($connection)->toBe('sqlite');

    $database = config("database.connections.{$connection}.database");
    expect($database)->toBe(':memory:');
});

test('mysql connection is not used', function () {
    $defaultConnection = DB::getDefaultConnection();
    expect($defaultConnection)->not->toBe('mysql');
});

test('can create tables in memory database', function () {
    $this->artisan('migrate')->assertSuccessful();

    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
    $tableNames = array_column($tables, 'name');

    expect($tableNames)->toContain('migrations');
});
