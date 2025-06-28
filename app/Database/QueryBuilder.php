<?php

namespace App\Database;

use PDO;
use PDOStatement;
use Exception;
use PDOException;



class QueryBuilder
{
    protected ?PDO $pdo;
    protected string $table;
    protected array $selects = [];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $joins = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected string $type = 'select';


    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }


    public function table(string $table): self
    {
        $this->table = $table;
        $this->resetQuery();
        return $this;
    }


    public function select(...$columns): self
    {
        $this->selects = array_merge($this->selects, $columns);
        return $this;
    }


    public function where(string $column, string $operator, mixed $value = null, string $boolean = 'and'): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
        $this->bindings[] = $value;
        return $this;
    }


    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }


    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function innerJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'INNER');
    }


    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }


    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }


    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }


    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $this->type = 'select';
        $sql = $this->buildSelectQuery();
        return $this->runQuery($sql, $this->bindings)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(mixed $id, array $columns = ['*']): ?array
    {
        return $this->where('id', $id)->select(...$columns)->limit(1)->get()[0] ?? null;
    }


    public function insert(array $data): bool
    {
        $this->type = 'insert';
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->bindings = array_values($data);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        return $this->runQuery($sql, $this->bindings)->rowCount() > 0;
    }


    public function update(array $data): int
    {
        $this->type = 'update';
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = ?";
            $this->bindings[] = $value;
        }
        $setClause = implode(', ', $setClauses);


        $whereBindings = [];
        foreach ($this->wheres as $where) {
            $whereBindings[] = $where['value'];
        }
        $this->bindings = array_merge(array_slice($this->bindings, 0, count($data)), $whereBindings);


        $sql = "UPDATE {$this->table} SET {$setClause}" . $this->buildWhereClause();

        return $this->runQuery($sql, $this->bindings)->rowCount();
    }


    public function delete(): int
    {
        $this->type = 'delete';
        $sql = "DELETE FROM {$this->table}" . $this->buildWhereClause();
        return $this->runQuery($sql, $this->bindings)->rowCount();
    }


    protected function buildSelectQuery(): string
    {
        $columns = empty($this->selects) ? '*' : implode(', ', $this->selects);
        $sql = "SELECT {$columns} FROM {$this->table}";

        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        // Add where clauses
        $sql .= $this->buildWhereClause();

        // Add order by clauses
        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . implode(', ', array_map(function ($order) {
                return "{$order['column']} {$order['direction']}";
            }, $this->orders));
        }

        // Add limit and offset
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }


    protected function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $whereClause = " WHERE ";
        foreach ($this->wheres as $index => $where) {
            if ($index > 0) {
                $whereClause .= " {$where['boolean']} ";
            }
            $whereClause .= "{$where['column']} {$where['operator']} ?";
        }
        return $whereClause;
    }



    protected function runQuery(string $sql, array $bindings): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $this->resetQuery(); // Reset query parts after execution
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Error al ejecutar la consulta: " . $e->getMessage() . " SQL: " . $sql . " Bindings: " . json_encode($bindings));
        }
    }


    protected function resetQuery(): void
    {
        $this->selects = [];
        $this->wheres = [];
        $this->bindings = [];
        $this->joins = [];
        $this->orders = [];
        $this->limit = null;
        $this->offset = null;
    }
}
