<?php

namespace Thunder\Database;

interface ConnectionInterface{
    public function prepare(string $sql): \PDOStatement;
    public function execute(\PDOStatement $statement, array $bindings = []): bool;
}