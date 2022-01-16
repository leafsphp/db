<?php

declare(strict_types=1);

namespace Leaf;

use Leaf\Db\Builder;
use Leaf\Db\Utils;

/**
 * Leaf Db
 * -----
 * Simple database interactions
 * 
 * @version 3.0
 * @since v2.1.0
 */
class Db extends Db\Core
{
    /**
     * Create a database if it doesn't exist
     * 
     * @param string $db The name of the database to create
     */
    public function create(string $db): self
    {
        $this->query("CREATE DATABASE $db");
        return $this;
    }

    /**
     * Drop a database if it exists
     * 
     * @param string $db The name of the database to drop
     */
    public function drop(string $db): self
    {
        $this->query("DROP DATABASE $db");
        return $this;
    }

    /**
     * Add a find by id clause to query
     * 
     * @param string|int $id The id of the row to find
     */
    public function find($id)
    {
        $this->where('id', $id);
        return $this->first();
    }

    /**
     * Find the first matching item for current query
     */
    public function first()
    {
        $this->query .= ' ORDER BY id ASC LIMIT 1';
        return $this->fetchAssoc();
    }

    /**
     * Find the last matching item for current query
     */
    public function last()
    {
        $this->query .= ' ORDER BY id DESC LIMIT 1';
        return $this->fetchAssoc();
    }

    /**
     * Order query items by a specific 
     * 
     * @param string $column The column to order results by
     * @param string $direction The direction to order [DESC, ASC]
     */
    public function orderBy(string $column, string $direction = 'desc')
    {
        $this->query = Builder::orderBy($this->query, $column, $direction);
        return $this;
    }

    /**
     * Limit query items by a specific number
     * 
     * @param string|number $limit The number to limit by
     */
    public function limit($limit)
    {
        $this->query = Builder::limit($this->query, $limit);
        return $this;
    }

    /**
     * Retrieve a row from table
     * 
     * @param string $table Db Table
     * @param string $items Specific table columns to fetch
     */
    public function select(string $table, string $items = "*")
    {
        $this->query("SELECT $items FROM $table");
        $this->table = $table;
        return $this;
    }

    /**
     * Add a new row in a db table
     * 
     * @param string $table Db Table
     */
    public function insert(string $table): self
    {
        $this->query("INSERT INTO $table");
        $this->table = $table;
        return $this;
    }

    /**
     * Update a row in a db table
     * 
     * @param string $table Db Table
     */
    public function update(string $table): self
    {
        $this->query("UPDATE $table");
        $this->table = $table;
        return $this;
    }

    /**
     * Delete a table's records
     * 
     * @param string $table: Db Table
     */
    public function delete(string $table): self
    {
        $this->query("DELETE FROM $table");
        $this->table = $table;
        return $this;
    }

    /**
	 * Pass in parameters into your query
	 * 
	 * @param array|string $params Key or params to pass into query
     * @param string|null $value Value for key 
	 */
	public function params($params): self
    {
        $this->query = Builder::params($this->query, $params);
        $this->bind(...(Builder::$bindings));
        $this->params = $params;
        return $this;
    }

    /**
     * Add a where clause to db query
     * 
     * @param string|array $condition The condition to evaluate
     * @param mixed $comparator Condition value or comparator
     * @param mixed $value The value of condition if comparator is passed
     */
    public function where($condition, $comparator = null, $value = null): self
    {
        $this->query = Builder::where(
            $this->query,
            $condition,
            $value === null ? $comparator : $value,
            $value === null ? "=" : $comparator
        );
        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Add a where clause with OR comparator to db query
     * 
     * @param string|array $condition The condition to evaluate
     * @param mixed $comparator Condition value or comparator
     * @param mixed $value The value of condition if comparator is passed
     */
    public function orWhere($condition, $comparator = null, $value = null): self
    {
        $this->query = Builder::where(
            $this->query,
            $condition,
            $value === null ? $comparator : $value,
            $value === null ? "=" : $comparator,
            "OR"
        );
        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Hide particular fields from the final value returned
     * 
     * @param mixed $values The value(s) to hide
     */
    public function hidden(...$values): self
    {
        $this->hidden = Utils::flatten($values);
        return $this;
    }

    /**
     * Make sure a value doesn't already exist in a table to avoid duplicates.
     * 
     * @param mixed $uniques Items to check for
     */
    public function unique(...$uniques)
    {
        $this->uniques = Utils::flatten($uniques);
        return $this;
    }

    /**
     * Add particular fields to the final value returned
     * 
     * @param string|array $name What to add
     * @param string $value The value to add
     */
    public function add($name, $value = null): self
    {
        if (is_array($name)) {
            $this->added = $name;
        } else {
            $this->added[$name] = $value;
        }

        return $this;
    }

    /**
     * Search a db table for a value
     * 
     * @param string $row The item to search for in table
     * @param string $value The keyword to search for
     * @param array|null $hidden The items to hide from returned result
     */
    public function search(string $row, string $value, ?array $hidden = []): ?array
    {
        return $this->select($this->table)->where($row, 'LIKE', Utils::includes($value))->hidden($hidden)->all();
    }
}
