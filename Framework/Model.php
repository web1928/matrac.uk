<?php

namespace Matrac\Framework;

use Exception;
use PDO;
use PDOStatement;
use PDOException;

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
    protected static function getDb(): PDO
    {
        return getDbConnection();
    }

    public static function beginTransaction(): void
    {
        $pdo = static::getDb();
        $pdo->beginTransaction();
    }

    public static function commitTransaction(): void
    {
        $pdo = static::getDb();
        $pdo->commit();
    }

    public static function rollbackTransaction(): void
    {
        $pdo = static::getDb();
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    /**
     * Executes a DB Select query
     *
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    protected static function query(string $sql = '', array $params = []): PDOStatement
    {
        $pdo = static::getDb();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Find record by primary key
     *
     * @param integer $id
     * @return array
     */
    public static function find(int $id): array
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
     * Undocumented function
     *
     * @return array
     */
    public static function all(): array
    {
        $table = static::$table;
        $stmt = static::query("SELECT * FROM {$table}");

        return $stmt->fetchAll();
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return integer
     */
    public static function insert(array $data): int
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

        return (int)static::getDb()->lastInsertId();  // ← Cast to int
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param array $data
     * @return integer
     */
    public static function update(int $id, array $data = []): int
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

        return (int)static::find($id);
    }

    /**
     * Delete record
     */
    public static function delete(int $id): void
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
    public static function where(string $column, string $operator, mixed $value = null): array
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
