<?php

namespace Thunder\Queue;

use Thunder\Database\ConnectionInterface;

class DatabaseQueue implements QueueInterface
{
    public function __construct(private readonly ConnectionInterface $connection){}

    public function push(object $job): void{
        $payload = serialize($job);
        $statement = $this->connection->prepare(
            "INSERT INTO jobs (payload) VALUES (?)"
        );
        $this->connection->execute($statement, [$payload]);
    }

    public function pop(): ?object{
        $statement = $this->connection->prepare(
            "SELECT * FROM jobs ORDER BY id ASC LIMIT 1"
        );
        $this->connection->execute($statement);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if(!$row) return null;

        $delete = $this->connection->prepare("DELETE FROM jobs where id = ?");
        $this->connection->execute($delete, [$row['id']]);

        return unserialize($row['payload']);
    }

    public function size(): int{
        $statement = $this->connection->prepare("SELECT COUNT(*) FROM jobs");
        $this->connection->execute($statement);
        return (int) $statement->fetchColumn();
    }
}