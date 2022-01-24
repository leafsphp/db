<?php

declare(strict_types=1);

namespace Leaf\Db;

/**
 * Leaf Db Query Builder
 * -------------------------
 * Functionality of leaf query builder.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 1.0.0
 */
class Builder
{
    /**
     * Params bound to query
     */
    public static $bindings = [];

    /**
     * Order query results by a colum
     * 
     * @param string $query The query to modify (if any)
     * @param string $column The column to order results by
     * @param string $direction The direction to order [DESC, ASC]
     */
    public static function orderBy(
        string $query,
        string $column,
        string $direction = 'desc'
    ): string {
        if (strpos($query, 'ORDER BY') === false) {
            $query .= " ORDER BY $column " . strtoupper($direction);
        } else {
            $parts = explode('ORDER BY', $query);
            $col = explode(' ', trim($parts[1]));
            $parts[1] = str_replace($col[0], '', $parts[1]);

            $query = implode("ORDER BY $column " . strtoupper($direction), $parts);
        }

        return $query;
    }

    /**
     * Limit query to specific number of values to return
     * 
     * @param string $query The query to modify (if any)
     * @param string|number $number Limit to query
     */
    public static function limit(string $query, $number): string
    {
        if (strpos($query, ' LIMIT ') === false) {
            $query .= " LIMIT $number";
        } else {
            $parts = explode(' LIMIT ', $query);
            $num = explode(' ', trim($parts[1]));
            $parts[1] = str_replace($num, '', $parts[1]);

            $query = implode(" LIMIT $number ", $parts);
        }

        return $query;
    }

    /**
     * Controls inner workings of all where blocks
     * 
     * @param string $query The query to modify
     * @param string|array $condition The condition to evaluate
     * @param mixed $value The value if condition is a string
     * @param string $comparator The comparator to bind condition
     * @param string $operation The operation to join multiple wheres
     */
    public static function where(
        string $query,
        $condition,
        $value = null,
        string $comparator = "=",
        string $operation = "AND"
    ): string {
        $query .= (strpos($query, ' WHERE ') === false) ? ' WHERE ' : " $operation ";

        if (is_string($condition)) {
            $query .= $condition;
            if ($value !== null) {
                $query .= " $comparator ?";
                static::$bindings[] = $value;
            }
        } else {
            foreach ($condition as $k => $v) {
                $query .=  "$k$comparator? $operation ";
            }

            $values = array_values($condition);
            $query = rtrim($query, " $operation ");

            static::$bindings = array_merge(static::$bindings, $values);
        }

        return $query;
    }

    /**
     * Builder for params block
     * 
     * @param string $query The query to modify
     * @param array|string $params Key or params to pass into query
     */
    public static function params(string $query, $params): string
    {
        $IS_UPDATE = is_int(strpos($query, 'UPDATE '));
        $IS_INSERT = is_int(strpos($query, 'INSERT INTO '));

        $query .= $IS_UPDATE ? ' SET ' : ' ';

        if ($IS_INSERT) {
            if (is_array($params[0] ?? null)) {
                $flat = Utils::flatten($params, true);
                $flatValues = Utils::flatten($params);
                $values = [];
                $keys = implode(',', array_keys($flat));

                foreach ($params as $v) {
                    $values[] = '(' . rtrim(str_repeat('?,', count(array_values($v))), ',') . ')';
                }

                static::$bindings = $flatValues;
                $values = implode(',', $values);

                $query .= "($keys) VALUES $values";
            } else {
                $keys = implode(',', array_keys($params));
                $values = array_values($params);
                static::$bindings = $values;

                $values = rtrim(str_repeat('?,', count($values)), ',');

                $query .= "($keys) VALUES ($values)";
            }
        }

        if ($IS_UPDATE) {
            $rebuild = [];

            foreach ($params as $k => $v) {
                $rebuild[$k] = '?';
            }

            $query .= str_replace('%3F', '?', http_build_query($rebuild, '', ', '));
            static::$bindings = array_values($params);
        }

        return $query;
    }
}
