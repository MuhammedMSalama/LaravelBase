<?php

namespace MuhammedSalama\Base\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;
use RuntimeException;

class CreateDatabaseCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'base:create-database
        {--connection= : The connection to use (defaults to the configured default)}';

    /**
     * @var string
     */
    protected $description = 'Create the configured database if it does not exist (supports MySQL & PostgreSQL)';

    public function handle(): int
    {
        $connection = (string) ($this->option('connection') ?: config('database.default'));
        $config     = config("database.connections.{$connection}");

        if (! is_array($config)) {
            $this->error("Connection [{$connection}] is not configured.");
            return self::FAILURE;
        }

        $driver   = (string) ($config['driver'] ?? '');
        $database = (string) ($config['database'] ?? '');

        if (! in_array($driver, ['mysql', 'pgsql'], true)) {
            $this->error("Only 'mysql' and 'pgsql' are supported. Current driver: " . ($driver ?: 'none'));
            return self::FAILURE;
        }

        if ($database === '') {
            $this->error('No database name is set for this connection.');
            return self::FAILURE;
        }

        try {
            return $driver === 'mysql'
                ? $this->createMysql($config, $database)
                : $this->createPgsql($config, $database);
        } catch (PDOException $e) {
            $this->error('Database operation failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function createMysql(array $config, string $database): int
    {
        $host      = (string) ($config['host']      ?? '127.0.0.1');
        $port      = (int) ($config['port']         ?? 3306);
        $charset   = (string) ($config['charset']   ?? 'utf8mb4');
        $collation = (string) ($config['collation'] ?? 'utf8mb4_unicode_ci');

        $pdo = $this->connect("mysql:host={$host};port={$port}", $config);

        $statement = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
        if ($statement === false) {
            throw new RuntimeException('Could not prepare the existence check.');
        }
        $statement->execute([$database]);

        if ($statement->fetchColumn() !== false) {
            $this->info("Database '{$database}' already exists. Nothing to do.");
            return self::SUCCESS;
        }

        $safe = str_replace('`', '', $database);
        $pdo->exec("CREATE DATABASE `{$safe}` CHARACTER SET {$charset} COLLATE {$collation}");

        $this->info("✔ Created MySQL database '{$database}' (charset {$charset}).");
        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function createPgsql(array $config, string $database): int
    {
        $host    = (string) ($config['host']    ?? '127.0.0.1');
        $port    = (int) ($config['port']       ?? 5432);
        $charset = (string) ($config['charset'] ?? 'utf8');

        $pdo = $this->connect("pgsql:host={$host};port={$port};dbname=postgres", $config);

        $statement = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
        if ($statement === false) {
            throw new RuntimeException('Could not prepare the existence check.');
        }
        $statement->execute([$database]);

        if ($statement->fetchColumn() !== false) {
            $this->info("Database '{$database}' already exists. Nothing to do.");
            return self::SUCCESS;
        }

        $safe = '"' . str_replace('"', '', $database) . '"';
        $pdo->exec("CREATE DATABASE {$safe} ENCODING '{$charset}'");

        $this->info("✔ Created PostgreSQL database '{$database}' (encoding {$charset}).");
        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function connect(string $dsn, array $config): PDO
    {
        $username = isset($config['username']) ? (string) $config['username'] : null;
        $password = isset($config['password']) ? (string) $config['password'] : null;

        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
