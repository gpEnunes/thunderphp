<?php

namespace Thunder\Database;

class Migrator
{
    public function __construct(private readonly ConnectionInterface $connection){}
    public function run(): void//Runs all pending migrations
    {
        $this->ensureMigrationsTableExists();
        $pending = $this->getPending();

        foreach ($pending as $file) {
            $migration = $this->resolve($file);
            $migration->up();
            //record it in migration table
            $stmt = $this->connection->prepare(
                "INSERT INTO migrations (migration) VALUES (?)"
            );
            $this->connection->execute($stmt, [pathinfo($file, PATHINFO_FILENAME)]);
            echo "Migrated : $file\n";
        }
    }
    public function rollback(): void//Reverses the last migration
    {
        $stmt = $this->connection->prepare(
            "SELECT migration FROM migrations ORDER BY id DESC LIMIT 1"
        );
        $this->connection->execute($stmt);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            echo "Nothing to rollback.\n";
            return;
        }

        $file = __DIR__ . '/../../../database/migrations/' . $row['migration'] . '.php';
        $migration = $this->resolve($file);
        $migration->down();

        $delete = $this->connection->prepare("DELETE FROM migrations WHERE migration = ?");
        $this->connection->execute($delete, [$row['migration']]);
        echo "Rolled back: {$row['migration']}\n";
    }
    private function getRan(): array //returns migration names already in DB
    {
        $stmt = $this->connection->prepare("SELECT migration FROM migrations");
        $this->connection->execute($stmt);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'migration');
    }
    private function getPending(): array //returns migration files not yet run
    {
        $files = glob(__DIR__ . '/../../../database/migrations/*.php');
        $ran = $this->getRan();

        return array_filter($files, function($file) use ($ran) {
            $name = pathinfo($file, PATHINFO_FILENAME); // e.g. "001_CreateJobsTable"
            return !in_array($name, $ran);
        });
    }
    private function resolve(string $file): MigrationInterface //Instantiates a migration class from filename
    {
        require_once $file;
        // filename "001_CreateJobsTable.php" → class name after the underscore
        $class = substr(pathinfo($file, PATHINFO_FILENAME), 4); // strips "001_"
        return new $class();
    }
    private function ensureMigrationsTableExists(): void //creates migrations table if not present
    {
        $stmt = $this->connection->prepare(
            "CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                run_at TIMESTAMP DEFAULT NOW()
                )
        ");
        $this->connection->execute($stmt);
    }
}