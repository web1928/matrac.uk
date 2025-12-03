<?php

declare(strict_types=1);

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
    public static function logGoodsReceipt(int $batchId, float $quantity, int $userId): int
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

    // public static function logTransaction(
    //     string $transactionType,
    //     int $batchId,
    //     int $stageId,
    //     float $quantity,
    //     int $userId,
    //     ?string $notes,
    //     string $actionType
    // ): void {
    //     static::query(
    //         "INSERT INTO `transactions` (
    //             `transaction_type`,
    //             `batch_id`,
    //             `from_stage_id`,
    //             `to_stage_id`,
    //             `quantity`,
    //             `user_id`,
    //             `notes`
    //          ) VALUES (?, ?, ?, NULL, ?, ?, ?)",
    //         [
    //             $transactionType,
    //             $batchId,
    //             $stageId,
    //             $quantity,
    //             $userId,
    //             $notes ?: "QA action: {$actionType}"
    //         ]
    //     );
    // }


    /**
     * Logs the transaction detail to the transaction tables
     *
     * @param string $transactionType
     * @param integer $batchId
     * @param integer $stageId
     * @param float $quantity
     * @param integer $userId
     * @param string|null $notes
     * @param string $actionType
     * @return integer
     */
    public static function logTransaction(
        string $transactionType,
        int $batchId,
        int $stageId,
        float $quantity,
        int $userId,
        ?string $notes,
        string $actionType
    ): int {
        return static::insert(
            [
                'transaction_type' => $transactionType,
                'batch_id' => $batchId,
                'from_stage_id' => $stageId,
                'to_stage_id' => NULL,
                'quantity' => $quantity,
                'user_id' => $userId,
                'notes' => $notes ?: "QA action: {$actionType}"
            ]
        );
    }

    public static function getTransactionHistory(int $batchId): array
    {
        $stmt = static::query(
            "SELECT 
                    t.transaction_id,
                    t.transaction_type,
                    t.quantity,
                    t.notes,
                    t.created_at,
                    st_from.stage_name as from_stage,
                    st_to.stage_name as to_stage,
                    u.username,
                    u.first_name,
                    u.last_name
                 FROM transactions t
                 LEFT JOIN stage st_from ON t.from_stage_id = st_from.stage_id
                 LEFT JOIN stage st_to ON t.to_stage_id = st_to.stage_id
                 JOIN users u ON t.user_id = u.user_id
                 WHERE t.batch_id = ?
                 ORDER BY t.created_at DESC",
            [$batchId]
        );

        return $stmt->fetchAll();
    }
}
