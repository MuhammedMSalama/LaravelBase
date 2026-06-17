<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Tests\Feature;

use MuhammedSalama\Base\Tests\TestCase;

class CreateDatabaseCommandTest extends TestCase
{
    public function test_command_fails_for_unconfigured_connection(): void
    {
        $this->artisan('base:create-database', ['--connection' => 'nonexistent_connection'])
            ->assertFailed()
            ->expectsOutputToContain('is not configured');
    }

    public function test_command_fails_for_unsupported_driver(): void
    {
        config(['database.connections.baddriver' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);

        $this->artisan('base:create-database', ['--connection' => 'baddriver'])
            ->assertFailed()
            ->expectsOutputToContain('Only \'mysql\' and \'pgsql\' are supported');
    }

    public function test_command_fails_when_database_name_is_empty(): void
    {
        config(['database.connections.nodatabase' => [
            'driver' => 'mysql',
            'database' => '',
            'host' => '127.0.0.1',
            'port' => 3306,
        ]]);

        $this->artisan('base:create-database', ['--connection' => 'nodatabase'])
            ->assertFailed()
            ->expectsOutputToContain('No database name is set');
    }

    public function test_command_rejects_database_name_with_invalid_characters(): void
    {
        config(['database.connections.badname' => [
            'driver' => 'mysql',
            'database' => 'bad name; DROP DATABASE',
            'host' => '127.0.0.1',
            'port' => 3306,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        $this->artisan('base:create-database', ['--connection' => 'badname'])
            ->assertFailed()
            ->expectsOutputToContain('invalid characters');
    }

    public function test_command_rejects_charset_with_invalid_characters(): void
    {
        config(['database.connections.badcharset' => [
            'driver' => 'mysql',
            'database' => 'myapp',
            'host' => '127.0.0.1',
            'port' => 3306,
            'charset' => "utf8'; INJECT",
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        $this->artisan('base:create-database', ['--connection' => 'badcharset'])
            ->assertFailed()
            ->expectsOutputToContain('invalid characters');
    }
}
