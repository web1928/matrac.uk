<?php

declare(strict_types=1);

namespace App\Models;

use Matrac\Framework\Model;

/**
 * Batch Model
 * Handles batch creation and code generation
 */
class Batch extends Model
{
    protected static $table = 'batch';
    protected static $primaryKey = 'batch_id';

    /**
     * Create new batch with goods receipt
     * 
     * @param array $data Batch data
     * @param int $userId User creating the batch
     * @return array Created batch with ID and code
     */
    /**
     * Create new batch with goods receipt
     * 
     * @param array $data Batch data
     * @param int $userId User creating the batch
     * @return array Created batch with ID and code
     */
    public static function createFromReceipt(array $data, int $userId): array
    {
        // Generate internal batch code
        $batchCode = static::generateBatchCode();

        // Helper function to convert empty strings to null
        $nullIfEmpty = function ($value) {
            return !empty($value) ? $value : null;
        };

        // Insert batch record
        $batchId = static::insert([
            'internal_batch_code' => $batchCode,
            'material_id' => $data['material_id'],
            'supplier_id' => $nullIfEmpty($data['supplier_id'] ?? null),
            'supplier_useby_1' => $nullIfEmpty($data['supplier_useby_1'] ?? null),
            'supplier_batch_code_1' => $nullIfEmpty($data['supplier_batch_code_1'] ?? null),
            'supplier_useby_2' => $nullIfEmpty($data['supplier_useby_2'] ?? null),
            'supplier_batch_code_2' => $nullIfEmpty($data['supplier_batch_code_2'] ?? null),
            'supplier_useby_3' => $nullIfEmpty($data['supplier_useby_3'] ?? null),
            'supplier_batch_code_3' => $nullIfEmpty($data['supplier_batch_code_3'] ?? null),
            'supplier_useby_4' => $nullIfEmpty($data['supplier_useby_4'] ?? null),
            'supplier_batch_code_4' => $nullIfEmpty($data['supplier_batch_code_4'] ?? null),
            'delivered_quantity' => $data['quantity'],
            'delivered_qty_uom' => $data['uom'],
            'po_number' => $nullIfEmpty($data['po_number'] ?? null),
            'haulier_name' => $nullIfEmpty($data['haulier_name'] ?? null),
            'delivery_note_ref' => $nullIfEmpty($data['delivery_note_ref'] ?? null),
            'silo_no' => $nullIfEmpty($data['silo_no'] ?? null),
            'coc_coa_attached' => $nullIfEmpty($data['coc_coa_attached'] ?? null),
            'rma_sheet_completed' => $nullIfEmpty($data['rma_sheet_completed'] ?? null),
            'matches_delivery_note' => $nullIfEmpty($data['matches_delivery_note'] ?? null),
            'bookin_confirmation_no' => $nullIfEmpty($data['bookin_confirmation_no'] ?? null),
            'receipt_comments' => $nullIfEmpty($data['receipt_comments'] ?? null),
            'user_id' => $userId
        ]);

        return [
            'batch_id' => $batchId,
            'batch_code' => $batchCode
        ];
    }

    /**
     * Generate internal batch code in format yy-jjj-nnn
     * yy = 2-digit year
     * jjj = Julian day of year (001-366)
     * nnn = Sequential number for the day (001-999)
     */
    public static function generateBatchCode(): string
    {
        // Get current date
        (string)$year = date('y');        // 2-digit year (e.g., 25)
        (int)$julianDay = date('z') + 1; // Day of year (1-366), +1 because it's 0-indexed
        (string)$julianDay = str_pad((string)$julianDay, 3, '0', STR_PAD_LEFT);

        // Get today's batch count to determine sequential number
        (string)$todayPrefix = "{$year}-{$julianDay}-";

        $stmt = static::query(
            "SELECT `internal_batch_code` 
             FROM `batch` 
             WHERE `internal_batch_code` LIKE ? 
             ORDER BY `internal_batch_code` DESC 
             LIMIT 1",
            [$todayPrefix . '%']
        );

        $lastBatch = $stmt->fetch();

        if ($lastBatch) {
            // Extract the sequence number from last batch (last 3 digits)
            (int)$lastSequence = (int)substr($lastBatch['internal_batch_code'], -3);
            error_log(gettype($lastSequence));
            (int)$nextSequence = $lastSequence + 1;
        } else {
            // First batch of the day
            (int)$nextSequence = 1;
        }

        // Pad sequence to 3 digits
        (string)$sequence = str_pad((string)$nextSequence, 3, '0', STR_PAD_LEFT);

        // Format: yy-jjj-nnn
        return (string) "{$todayPrefix}-{$sequence}";
    }

    /**
     * Get recent receipts for today
     */
    public static function getTodayReceipts(): array
    {
        $stmt = static::query(
            "SELECT 
                b.batch_id,
                b.internal_batch_code,
                b.delivered_quantity,
                b.delivered_qty_uom,
                b.receipt_date,
                m.code as material_code,
                m.description as material_description,
                s.supplier_name
             FROM batch b
             JOIN material m ON b.material_id = m.material_id
             LEFT JOIN supplier s ON b.supplier_id = s.supplier_id
             WHERE DATE(b.receipt_date) = CURDATE()
             ORDER BY b.receipt_date DESC
             LIMIT 20"
        );

        return $stmt->fetchAll();
    }

    /**
     * Returns the number of Goods Receipts for the current day
     *
     * @return integer
     */
    public static function getTodayReceiptCount(): int
    {
        $stmt = static::query(
            "SELECT COUNT(*) as count 
             FROM batch 
             WHERE DATE(receipt_date) = CURDATE()"
        );

        return (int) $stmt->fetch()['count'];
    }

    public static function getActiveBatchCount(): int
    {
        $stmt = static::query(
            "SELECT COUNT(DISTINCT b.batch_id) as count
             FROM batch b
             JOIN inventory i ON b.batch_id = i.batch_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code != 'REJECTED'"
        );

        return (int) $stmt->fetch()['count'];
    }


    /**
     * Returns a list array of the 5 most recently receipted materials
     *
     * @return array
     */
    public static function getRecentReceipts(): array
    {
        $stmt = static::query(
            "SELECT 
                b.internal_batch_code,
                b.delivered_quantity,
                b.delivered_qty_uom,
                b.receipt_date,
                m.code as material_code,
                m.description as material_description,
                s.supplier_name,
                CONCAT(u.first_name, ' ', u.last_name) as received_by
             FROM batch b
             JOIN material m ON b.material_id = m.material_id
             LEFT JOIN supplier s ON b.supplier_id = s.supplier_id
             JOIN users u ON b.user_id = u.user_id
             WHERE DATE(b.receipt_date) = CURDATE()
             ORDER BY b.receipt_date DESC
             LIMIT 5"
        );

        return $stmt->fetchAll();
    }

    public static function getBatchDetails(int $batchID): array
    {

        // Get batch details
        $stmt = static::query(
            "SELECT 
                    b.*,
                    m.code as material_code,
                    m.description as material_description,
                    m.base_uom,
                    m.material_group,
                    s.supplier_name,
                    s.contact_name,
                    u.username,
                    u.first_name,
                    u.last_name
                 FROM batch b
                 JOIN material m ON b.material_id = m.material_id
                 LEFT JOIN supplier s ON b.supplier_id = s.supplier_id
                 JOIN users u ON b.user_id = u.user_id
                 WHERE b.batch_id = ?",
            [$batchID]
        );

        return $stmt->fetch();
    }
}
