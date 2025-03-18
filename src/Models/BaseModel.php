<?php
/**
 *
 * ref: Eloquent ORM api
 *
 */

namespace LinkGallery\Models;

class BaseModel
{
    protected $dbConn;
    protected $table;
    protected $select = '*';
    protected $where = [];
    protected $orderBy = [];
    protected $limit = null;
    protected $offset = null;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function table($table)
    {
        $this->table = $this->dbConn->prefix . $table;
        return $this;
    }

    public function select($columns)
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where[] = [$key, '=', $val];
            }
        } else {
            if ($value === null) {
                $value = $operator;
                $operator = '=';
            }
            $this->where[] = [$column, $operator, $value];
        }
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = [$column, strtoupper($direction)];
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = (int)$offset;
        return $this;
    }

    public function get()
    {
        $query = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->where)) {
            $whereClauses = [];
            foreach ($this->where as $condition) {
                list($column, $operator, $value) = $condition;
//                $whereClauses[] = $this->dbConn->prepare("%s %s %s", $column, $operator, $value);
                $whereClauses[] = $this->dbConn->prepare("{$column} {$operator} %s", $value);
            }
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }

        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $order) {
                list($column, $direction) = $order;
                $orderClauses[] = "{$column} {$direction}";
            }
            $query .= " ORDER BY " . implode(', ', $orderClauses);
        }

        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $query .= " OFFSET {$this->offset}";
            }
        }
//        error_log('sql: ' . $query);

        return $this->dbConn->get_results($query);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    public function create($data)
    {
        return $this->dbConn->insert($this->table, $data);
    }

    public function update($data)
    {
        if (empty($this->where)) {
            return false;
        }
        return $this->dbConn->update($this->table, $data, $this->buildWhereConditions());
    }

    public function delete()
    {
        if (empty($this->where)) {
            return false;
        }
        return $this->dbConn->delete($this->table, $this->buildWhereConditions());
    }

    protected function buildWhereConditions()
    {
        $conditions = [];
        foreach ($this->where as $condition) {
            list($column, $operator, $value) = $condition;
            if ($operator === '=') {
                $conditions[$column] = $value;
            }
        }
        return $conditions;
    }
}
