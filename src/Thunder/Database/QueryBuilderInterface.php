<?php
namespace Thunder\Database;

interface QueryBuilderInterface{
    public function table(string $table):static;
    public function select(string ...$columns):static;
    //Filtering
    public function where(string $column, string $operator, mixed $value):static;
    public function orWhere(string $column, string $operator, mixed $value):static;
    public function whereIn(string $column, array $values):static;
    public function whereNull(string $column):static;
    public function whereNotNull(string $column):static;
    //Joins
    public function join(string $table, string $first, string $operator, string $second): static;
    public function leftJoin(string $table, string $first, string $operator, string $second): static;
    //Aggregates
    public function count(): int;
    public function max(string $column): mixed;
    public function min(string $column): mixed;
    public function avg(string $column): mixed;
    public function sum(string $column): mixed;
    //Pagination
    public function offset(int $offset): static;
    public function limit(int $limit):static;
    //Write operations
    public function insert(array $data): bool;
    public function update(array $data): bool;
    public function delete():bool;
    #Utility
    public function toSql():string;
    public function orderBy(string $column, string $direction = 'ASC'):static;
    public function get():array;
    public function first():array|null;
}