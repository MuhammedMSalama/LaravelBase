<?php

namespace MuhammedSalama\Base\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

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
        $connection = $this->option('connection') ?: config('database.default');
        $config     = config("database.connections.{$connection}");

        if (! $config) {
            $this->error("Connection [{$connection}] is not configured.");
            return self::FAILURE;
        }

        $driver   = $config['driver']   ?? null;
        $database = $config['database'] ?? null;

        if (! in_array($driver, ['mysql', 'pgsql'], true)) {
            $this->error("Only 'mysql' and 'pgsql' are supported. Current driver: " . ($driver ?? 'null'));
            return self::FAILURE;
        }

        if (empty($database)) {
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

    protected function createMysql(array $config, string $database): int
    {
        $host      = $config['host']      ?? '127.0.0.1';
        $port      = $config['port']      ?? 3306;
        $charset   = $config['charset']   ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

        // Connect to the server WITHOUT selecting a database.
        $pdo = new PDO(
            "mysql:host={$host};port={$port}",
            $config['username'] ?? null,
            $config['password'] ?? null
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $found = $pdo->query('SHOW DATABASES LIKE ' . $pdo->quote($database))->fetch();
        if ($found) {
            $this->info("Database '{$database}' already exists. Nothing to do.");
            return self::SUCCESS;
        }

        $safe = str_replace('`', '', $database);
        $pdo->exec("CREATE DATABASE `{$safe}` CHARACTER SET {$charset} COLLATE {$collation}");

        $this->info("✔ Created MySQL database '{$database}' (charset {$charset}).");
        return self::SUCCESS;
    }

    protected function createPgsql(array $config, string $database): int
    {
        $host    = $config['host']    ?? '127.0.0.1';
        $port    = $config['port']    ?? 5432;
        $charset = $config['charset'] ?? 'utf8';

        // Connect to the default maintenance database 'postgres'.
        $pdo = new PDO(
            "pgsql:host={$host};port={$port};dbname=postgres",
            $config['username'] ?? null,
            $config['password'] ?? null
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
        $stmt->execute([$database]);

        if ($stmt->fetchColumn()) {
            $this->info("Database '{$database}' already exists. Nothing to do.");
            return self::SUCCESS;
        }

        // Identifiers can't be bound; the name comes from trusted config. Quote it safely.
        $safe = '"' . str_replace('"', '', $database) . '"';
        $pdo->exec("CREATE DATABASE {$safe} ENCODING '{$charset}'");

        $this->info("✔ Created PostgreSQL database '{$database}' (encoding {$charset}).");
        return self::SUCCESS;
    }
}
