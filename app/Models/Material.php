<?php

declare(strict_types=1);

namespace App\Models;

use Matrac\Framework\Model;

/**
 * Material Model
 * Handles material data access
 */
class Material extends Model
{

    protected static $table = 'material';
    protected static $primaryKey = 'material_id';

    /**
     * Get all active materials
     */
    public static function getActive(): array
    {
        $stmt = static::query(
            "SELECT * FROM material WHERE active = 1 ORDER BY code ASC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Search materials by code or description
     * 
     * @param string $query Search term
     * @return array Matching materials
     */
    public static function search($query): array
    {
        if (strlen($query) < 3) {
            return [];
        }

        $searchPattern = "%{$query}%";

        $stmt = static::query(
            "SELECT 
                material_id,
                code,
                description,
                base_uom
             FROM material
             WHERE (code LIKE ? OR description LIKE ?)
               AND active = 1
             ORDER BY 
                CASE 
                    WHEN code LIKE ? THEN 1
                    WHEN code LIKE ? THEN 2
                    ELSE 3
                END,
                code ASC
             LIMIT 15",
            [
                $searchPattern,  // code LIKE
                $searchPattern,  // description LIKE
                $query,          // exact match priority
                $query . '%'     // starts with priority
            ]
        );

        return $stmt->fetchAll();
    }

    /**
     * Find material by ID
     */
    public static function findById($id): array
    {
        return static::find($id);
    }

    /**
     * Retruns the total number of material with on-hold status
     *
     * @return integer
     */
    public static function getActiveMaterialCount(): int
    {
        $stmt = static::query(
            "SELECT COUNT(*) as count 
             FROM material 
             WHERE active = 1"
        );

        return $stmt->fetch()['count'] ?? 0;
    }
}
