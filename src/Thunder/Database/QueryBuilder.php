<?php

namespace Thunder\Database;

class QueryBuilder implements QueryBuilderInterface
{
    private string $table = '';
    private array $columns = ['*'];
    private array $wheres = [];
    private array $orWheres = [];
    private array $whereIns = [];
    private array $whereNulls = [];
    private array $whereNotNulls = [];
    private array $joins = [];
    private array $bindings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $orderByColumn = null;
    private string $orderByDirection = 'ASC';

    public function __construct(private readonly ConnectionInterface $connection){}
    public function table(string $table): static{
        $this->table = $table;
        return $this;
    }

    public function select(string ...$columns): static{
        $this->columns = $columns;
        return $this;
    }
    public function whereIn(string $column, array $values): static {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->whereIns[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }
    public function toSql(): string {
        $sql = 'SELECT ' . implode(', ', $this->columns);
        $sql .= ' FROM ' . $this->table;

        if (!empty($this->joins))        $sql .= ' ' . implode(' ', $this->joins);
        $allWheres = array_merge(
            $this->wheres,
            $this->whereIns,
            $this->whereNulls,
            $this->whereNotNulls,
        );
        if (!empty($allWheres)) $sql .= ' WHERE ' . implode(' AND ', $allWheres);
        if (!empty($this->orWheres)) $sql .= ' OR ' . implode(' OR ', $this->orWheres);
        if (!empty($this->orderByColumn)) $sql .= " ORDER BY $this->orderByColumn $this->orderByDirection";
        if ($this->limit !== null)        $sql .= " LIMIT $this->limit";
        if ($this->offset !== null)       $sql .= " OFFSET $this->offset";

        return $sql;
    }

    public function where(string $column, string $operator, mixed $value): static {
        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): static
    {
        $this->orWheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function whereNull(string $column): static
    {
        // store "$column IS NULL" in $this->whereNulls
        $this->whereNulls[] = "$column IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): static
    {
        // store "$column IS NOT NULL" in $this->whereNotNulls
        $this->whereNotNulls[] = "$column IS NOT NULL";
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): static {
        $this->joins[] = "INNER JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "LEFT JOIN $table ON $first $operator $second";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        // set $this->orderByColumn and $this->orderByDirection
        $this->orderByColumn = $column;
        $this->orderByDirection = $direction;
        return $this;
    }

    public function limit(int $limit): static
    {
        // set $this->limit
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        // set $this->offset
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $statement = $this->connection->prepare($this->toSql());
        $this->connection->execute($statement, $this->bindings);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function first(): array|null
    {
        // add LIMIT 1 to toSql(), prepare, execute, fetch one row or return null
        $this->limit(1);
        $statement = $this->connection->prepare($this->toSql());
        $this->connection->execute($statement, $this->bindings);
        return $statement->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $statement = $this->connection->prepare($sql);
        return $this->connection->execute($statement, array_values($data));
    }

    public function update(array $data): bool
    {
        $setClauses = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $setClauses";
        if (!empty($this->wheres)) $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        $bindings = array_merge(array_values($data), $this->bindings);
        $statement = $this->connection->prepare($sql);
        return $this->connection->execute($statement, $bindings);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        $statement = $this->connection->prepare($sql);
        return $this->connection->execute($statement, $this->bindings);
    }

    public function count(): int
    {
        // SELECT COUNT(*) FROM table WHERE ..., return (int) result
        $statement = $this->connection->prepare("SELECT COUNT(*) FROM {$this->table}");
        $this->connection->execute($statement, $this->bindings);
        return (int) $statement->fetchColumn();
    }

    public function max(string $column): mixed
    {
        // SELECT MAX(column) FROM table
        $statement = $this->connection->prepare("SELECT MAX($column) FROM {$this->table}");
        $this->connection->execute($statement, $this->bindings);
        return (int) $statement->fetchColumn();
    }

    public function min(string $column): mixed {
        $statement = $this->connection->prepare("SELECT MIN($column) FROM {$this->table}");
        $this->connection->execute($statement, $this->bindings);
        return (int) $statement->fetchColumn();
    }
    public function avg(string $column): mixed {
        $statement = $this->connection->prepare("SELECT AVG($column) FROM {$this->table}");
        $this->connection->execute($statement, $this->bindings);
        return (int) $statement->fetchColumn();
    }
    public function sum(string $column): mixed {
        $statement = $this->connection->prepare("SELECT SUM($column) FROM {$this->table}");
        $this->connection->execute($statement, $this->bindings);
        return (int) $statement->fetchColumn();
    }


}