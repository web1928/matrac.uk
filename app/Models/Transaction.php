<?php

namespace App\Models;

use Matrac\Framework\Model;

/**
 * Transaction Model
 * Handles transaction logging
 */
class Transaction extends Model
{
    protected static $table = 'transactions';
    protected static $primaryKey = 'transaction_id';

    /**
     * Log goods receipt transaction
     * 
     * @param int $batchId Batch ID
     * @param float $quantity Quantity
     * @param int $userId User ID
     * @return int Transaction ID
     */
    public static function logGoodsReceipt($batchId, $quantity, $userId)
    {
        return static::insert([
            'transaction_type' => 'GR',
            'batch_id' => $batchId,
            'from_stage_id' => null,
            'to_stage_id' => 1,  // To Goods Receipt stage
            'quantity' => $quantity,
            'user_id' => $userId,
            'notes' => 'Goods receipt'
        ]);
    }
}
