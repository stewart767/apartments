<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        // Prevent running tests against the active MySQL development database
        if (config('database.default') === 'mysql' && config('database.connections.mysql.database') === 'apartments') {
            throw new \RuntimeException(
                "CRITICAL WARNING: Tests are running against your local development MySQL database ('apartments') instead of the SQLite in-memory database!\n" .
                "This is because the configuration is cached. Run 'php artisan config:clear' and 'php artisan route:clear' before running tests to prevent your database from being wiped."
            );
        }

        parent::setUp();
    }
}
