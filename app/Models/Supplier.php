<?php

namespace App\Models;

use Core\Model;

/**
 * Supplier Model
 * Handles supplier data access
 */
class Supplier extends Model
{
    protected static $table = 'supplier';
    protected static $primaryKey = 'supplier_id';

    /**
     * Get all active suppliers
     */
    public static function getActive()
    {
        $stmt = static::query(
            "SELECT * FROM supplier WHERE active = 1 ORDER BY supplier_name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Search suppliers by name
     * 
     * @param string $query Search term
     * @return array Matching suppliers
     */
    public static function search($query)
    {
        if (strlen($query) < 3) {
            return [];
        }

        $searchPattern = "%{$query}%";

        $stmt = static::query(
            "SELECT 
                supplier_id,
                supplier_name,
                contact_name
             FROM supplier
             WHERE supplier_name LIKE ?
               AND active = 1
             ORDER BY supplier_name ASC
             LIMIT 15",
            [$searchPattern]
        );

        return $stmt->fetchAll();
    }

    /**
     * Find supplier by ID
     */
    public static function findById($id)
    {
        return static::find($id);
    }
}
