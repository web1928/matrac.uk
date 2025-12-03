<?php

declare(strict_types=1);

namespace App\Models;

use Matrac\Framework\Model;

/**
 * HoldStatus Model
 * Manages inventory hold status types
 */
class HoldStatus extends Model
{
    protected static $table = 'hold_status';
    protected static $primaryKey = 'hold_status_id';

    // Status ID constants (match your database)
    public const AVAILABLE = 1;
    public const ON_HOLD = 2;
    public const REJECTED = 3;

    /**
     * Map user action to status ID
     * 
     * @param string $action User action type (hold|release|reject)
     * @return int Status ID
     * @throws \InvalidArgumentException if action is invalid
     */
    public static function getStatusIdFromAction(string $action): int
    {
        return match ($action) {
            'release' => self::AVAILABLE,
            'hold' => self::ON_HOLD,
            'reject' => self::REJECTED,
            default => throw new \InvalidArgumentException("Invalid action type: $action")
        };
    }

    /**
     * Get transaction type for action
     * 
     * @param string $action User action type
     * @return string Transaction type code
     */
    public static function getTransactionType(string $action): string
    {
        return match ($action) {
            'hold' => 'HOLD',
            'release' => 'RELEASE',
            'reject' => 'REJECT',
            default => throw new \InvalidArgumentException("Invalid action type: $action")
        };
    }

    /**
     * Check if status allows a specific action
     * 
     * @param string $currentCode Current status code
     * @param string $action Desired action
     * @return bool Whether action is allowed
     */
    public static function isActionAllowed(string $currentCode, string $action): bool
    {
        return match ([$currentCode, $action]) {
            ['AVAILABLE', 'hold'] => true,
            ['ON_HOLD', 'release'] => true,
            ['AVAILABLE', 'reject'] => true,
            ['ON_HOLD', 'reject'] => true,
            default => false
        };
    }
}
