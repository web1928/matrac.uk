<?php

namespace App\Models;

use Matrac\Framework\Model;

/**
 * Inventory Model
 * Handles inventory records
 */
class Inventory extends Model
{
    protected static $table = 'inventory';
    protected static $primaryKey = 'inventory_id';

    /**
     * Create inventory record from goods receipt
     * Stage 1 = Goods Receipt, Status 1 = Available
     * 
     * @param int $batchId Batch ID
     * @param float $quantity Quantity in base UOM
     * @return int Inventory ID
     */
    public static function createFromReceipt($batchId, $quantity)
    {
        return static::insert([
            'batch_id' => $batchId,
            'stage_id' => 1,  // Goods Receipt stage
            'hold_status_id' => 1,  // Available status
            'quantity' => $quantity
        ]);
    }
}
