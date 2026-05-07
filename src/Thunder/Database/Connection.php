<?php

namespace Thunder\Database;

class Connection implements ConnectionInterface
{
    public function __construct(private readonly \PDO $pdo){}

    public function prepare(string $sql): \PDOStatement{
        return $this->pdo->prepare($sql);
    }

    public function execute(\PDOStatement $statement, array $bindings = []): bool{
        return $statement->execute($bindings);
    }
}