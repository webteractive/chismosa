<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're using SQLite for testing - never MySQL
        $connection = DB::getDefaultConnection();
        if ($connection !== 'sqlite') {
            throw new \RuntimeException(
                "Tests must use SQLite, but '{$connection}' connection is active. ".
                'MySQL database will not be touched during tests.'
            );
        }

        // Double-check that we're using in-memory database
        $database = config("database.connections.{$connection}.database");
        if ($database !== ':memory:') {
            throw new \RuntimeException(
                "Tests must use in-memory SQLite database, but '{$database}' is configured. ".
                'This ensures MySQL database is never touched.'
            );
        }
    }
}
