<?php

namespace Thunder\Database;
use Thunder\Database\QueryBuilderInterface;
abstract class Repository implements RepositoryInterface
{

    abstract protected function getTable():string; //each subclass defines its own table
    public function __construct(protected readonly QueryBuilderInterface $queryBuilder){}
    public function findAll(): array
    {
        return $this->queryBuilder->table($this->getTable())->get();
    }

    public function delete(int $id): bool
    {
        return $this->queryBuilder->table($this->getTable())->where("id", "=", $id)->delete();
    }

    abstract public function findById(int $id): ?object;
    abstract public function create(object $model): bool;
    abstract public function update(int $id, object $model): bool;

}