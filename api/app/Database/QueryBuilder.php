<?php
declare(strict_types=1);

namespace App\Database;

use PDO;

class QueryBuilder
{
    private string $table;
    private array $conditions = [];
    private array $params = [];
    private string $select = '*';

    public function __construct(
        private PDO $db
    ) {}

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(string $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->conditions[] = "{$column} {$operator} ?";
        $this->params[] = $value;
        return $this;
    }

    public function whereEquals(string $column, mixed $value): self
    {
        return $this->where($column, '=', $value);
    }

    public function whereLike(string $column, string $value): self
    {
        return $this->where($column, 'LIKE', "%{$value}%");
    }

    public function get(): array
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $result = $this->get();
        return $result[0] ?? null;
    }
}
