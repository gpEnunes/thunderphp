<?php
namespace Thunder\Database;

interface RepositoryInterface{
    public function findById(int $id): ?object;
    public function findAll(): array;
    public function create(object $model): bool;
    public function update(int $id, object $model): bool;
    public function delete(int $id): bool;
}