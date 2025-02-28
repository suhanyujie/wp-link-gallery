<?php

namespace LinkGallery\Models;

abstract class Model
{
    protected $wpdb;
    protected $table;
    protected $data = [];

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->setTable();
    }

    abstract protected function setTable();

    public static function create(array $data)
    {
        $instance = new static;
        return $instance->insert($data);
    }

    public function insert(array $data)
    {
        $this->wpdb->insert($this->table, $data);
        return $this->find($this->wpdb->insert_id);
    }

    public function update(array $data)
    {
        if (!isset($this->data['id'])) {
            return false;
        }

        $this->wpdb->update(
            $this->table,
            $data,
            ['id' => $this->data['id']]
        );

        return $this->find($this->data['id']);
    }

    public static function find($id)
    {
        $instance = new static;
        $result = $instance->wpdb->get_row(
            $instance->wpdb->prepare(
                "SELECT * FROM {$instance->table} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if ($result) {
            $instance->data = $result;
            return $instance;
        }

        return null;
    }

    public static function all()
    {
        $instance = new static;
        return $instance->wpdb->get_results(
            "SELECT * FROM {$instance->table}",
            ARRAY_A
        );
    }

    public static function where($column, $value)
    {
        $instance = new static;
        $results = $instance->wpdb->get_results(
            $instance->wpdb->prepare(
                "SELECT * FROM {$instance->table} WHERE {$column} = %s",
                $value
            ),
            ARRAY_A
        );

        return $results;
    }

    protected $orderByClause = '';
    protected $limitClause = '';

    public static function orderBy($column, $direction = 'ASC')
    {
        $instance = new static;
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $instance->orderByClause = " ORDER BY {$column} {$direction}";
        return $instance;
    }

    public function limit($limit)
    {
        $this->limitClause = " LIMIT " . intval($limit);
        return $this;
    }

    public function get()
    {
        $query = "SELECT * FROM {$this->table}";
        if ($this->orderByClause) {
            $query .= $this->orderByClause;
        }
        if ($this->limitClause) {
            $query .= $this->limitClause;
        }
        $results = $this->wpdb->get_results($query);
        $instances = [];
        foreach ($results as $result) {
            $instance = new static;
            $instance->data = (array) $result;
            $instances[] = $instance;
        }
        return $instances;
    }

    public function delete()
    {
        if (!isset($this->data['id'])) {
            return false;
        }

        return $this->wpdb->delete(
            $this->table,
            ['id' => $this->data['id']]
        );
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public static function sorted()
    {
        $instance = new static;
        $instance->orderBy('sort_order', 'asc');
        return $instance;
    }
}