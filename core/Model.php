<?php

namespace Core;

/**
 * Base Model
 * Database access layer
 */
class Model
{
    protected static $table;
    protected static $primaryKey = 'id';

    /**
     * Get database connection
     */
    protected static function getDb()
    {
        return getDbConnection();
    }

    /**
     * Execute query
     */
    protected static function query($sql, $params = [])
    {
        return executeQuery($sql, $params);
    }

    /**
     * Find record by ID
     */
    public static function find($id)
    {
        $table = static::$table;
        $pk = static::$primaryKey;

        $stmt = static::query(
            "SELECT * FROM {$table} WHERE {$pk} = ? LIMIT 1",
            [$id]
        );

        return $stmt->fetch();
    }

    /**
     * Get all records
     */
    public static function all()
    {
        $table = static::$table;
        $stmt = static::query("SELECT * FROM {$table}");
        return $stmt->fetchAll();
    }

    /**
     * Insert record
     */
    public static function insert($data)
    {
        $table = static::$table;

        $columns = array_keys($data);
        $values = array_values($data);

        $columnsList = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        static::query(
            "INSERT INTO {$table} ({$columnsList}) VALUES ({$placeholders})",
            $values
        );

        return static::getDb()->lastInsertId();
    }

    /**
     * Update record
     */
    public static function update($id, $data)
    {
        $table = static::$table;
        $pk = static::$primaryKey;

        $sets = [];
        $values = [];

        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $setString = implode(', ', $sets);

        static::query(
            "UPDATE {$table} SET {$setString} WHERE {$pk} = ?",
            $values
        );

        return static::find($id);
    }

    /**
     * Delete record
     */
    public static function delete($id)
    {
        $table = static::$table;
        $pk = static::$primaryKey;

        static::query(
            "DELETE FROM {$table} WHERE {$pk} = ?",
            [$id]
        );
    }

    /**
     * Where clause
     */
    public static function where($column, $operator, $value = null)
    {
        $table = static::$table;

        // If only 2 params, assume = operator
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $stmt = static::query(
            "SELECT * FROM {$table} WHERE {$column} {$operator} ?",
            [$value]
        );

        return $stmt->fetchAll();
    }
}
