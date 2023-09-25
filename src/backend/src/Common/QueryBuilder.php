<?php

namespace App\Common;

class QueryBuilder
{
    private array $join;
    private array $where;
    private ?string $select = null;
    private ?string $table = null;
    private ?string $group = null;
    private ?string $order = null;
    private ?string $limit = null;

    public function __construct()
    {
        $this->join = [];
        $this->where = [];
    }

    public function select(string $select): self
    {
        $this->select = $select;

        return $this;
    }

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function join(string $sql): self
    {
        $this->join[] = [
            'type' => 'INNER',
            'sql' => $sql,
        ];

        return $this;
    }

    public function leftJoin(string $sql): self
    {
        $this->join[] = [
            'type' => 'LEFT',
            'sql' => $sql,
        ];

        return $this;
    }

    public function rightJoin(string $sql): self
    {
        $this->join[] = [
            'type' => 'RIGHT',
            'sql' => $sql,
        ];

        return $this;
    }

    public function where(string $condition): self
    {
        $this->where[] = [
            'condition' => $condition,
            'boolean' => 'AND',
        ];

        return $this;
    }

    public function orWhere(string $condition): self
    {
        $this->where[] = [
            'condition' => $condition,
            'boolean' => 'OR',
        ];

        return $this;
    }

    public function group(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function order(string $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function limit(string $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Build the SQL query string based on the set parameters.
     *
     * @return string
     */
    public function build(): string
    {
        $query = "FROM {$this->table}";

        if (null !== $this->select) {
            $query = "{$this->select} {$query}";
        }

        foreach ($this->join as $join) {
            $query .= " {$join['type']} JOIN {$join['sql']}";
        }

        if (count($this->where) > 0) {
            $query .= " WHERE ";

            foreach ($this->where as $key => $value) {
                if (0 < $key) {
                    $query .= " {$value['boolean']} ";
                }

                $query .= "{$value['condition']}";
            }
        }

        if (null !== $this->group) {
            $query .= " GROUP BY {$this->group}";
        }

        if (null !== $this->order) {
            $query .= " ORDER BY {$this->order}";
        }

        if (null !== $this->limit) {
            $query .= " {$this->limit}";
        }

        return $query;
    }
}