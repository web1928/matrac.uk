<?php

declare(strict_types=1);

namespace App\Models;

use Matrac\Framework\Model;

/**
 * Supplier Model
 * Handles supplier data access
 */
class Supplier extends Model
{

    protected static $table = 'supplier';
    protected static $primaryKey = 'supplier_id';

    /**
     * Return array of all active suppliers
     */
    public static function getActive(): array
    {
        $stmt = static::query(
            "SELECT * FROM supplier WHERE active = 1 ORDER BY supplier_name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Return searched list of searched suppliers
     * 
     * @param string $query Search term
     * @return array Matching suppliers
     */
    public static function search($query): array
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
    public static function findById($id): array
    {
        return static::find($id);
    }

    /**
     * Return count of active suppliers
     *
     * @return integer
     */
    public static function getActiveSupplierCount(): int
    {
        $stmt = static::query(
            "SELECT COUNT(*) as count 
             FROM supplier 
             WHERE active = 1"
        );

        return $stmt->fetch()['count'] ?? 0;
    }
}
