<?php

namespace Tests;

use RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application, forcing in-memory SQLite before any test
     * trait can touch a database so MySQL is never reached.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $connection = DB::getDefaultConnection();
        if ($connection !== 'sqlite') {
            throw new RuntimeException(
                "Tests must use SQLite, but '{$connection}' connection is active. ".
                'MySQL database will not be touched during tests.'
            );
        }

        $database = config("database.connections.{$connection}.database");
        if ($database !== ':memory:') {
            throw new RuntimeException(
                "Tests must use in-memory SQLite database, but '{$database}' is configured. ".
                'This ensures MySQL database is never touched.'
            );
        }
    }
}
