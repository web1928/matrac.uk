<?php

declare(strict_types=1);

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
     * Creates new inventory at Goods Receipt stage with Available status
     *
     * @param integer $batchId
     * @param float $quantity
     * @return integer
     */
    public static function createFromReceipt(int $batchId, float $quantity): int
    {
        return static::insert([
            'batch_id' => $batchId,
            'stage_id' => 1,  // Goods Receipt stage
            'hold_status_id' => 1,  // Available status
            'quantity' => $quantity
        ]);
    }

    /**
     * Get available inventory with optional filters
     * Excludes rejected items and zero quantities
     * 
     * @param string $filterMaterial
     * @param integer $filterStage
     * @param integer $filterStatus
     * @return array
     */
    public static function getAvailableInventory(string $filterMaterial, int $filterStage, int $filterStatus): array
    {

        // Extract material code from autocomplete format (CODE - Description) or use search term as-is
        if (str_contains($filterMaterial, " - ")) {
            $parts = explode(" - ", $filterMaterial);
            $materialSearchString = trim($parts[0]);
        } else {
            $materialSearchString = trim($filterMaterial);
        }

        // Build WHERE clauses
        $where = ["i.quantity > 0"];  // Only show non-zero inventory
        $where[] = "hs.status_code != 'REJECTED'";  // Exclude rejected
        $params = [];

        if (!empty($materialSearchString)) {
            $where[] = "(m.code LIKE ? OR m.description LIKE ?)";
            $searchPattern = "%{$materialSearchString}%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }

        if ($filterStage > 0) {
            $where[] = "i.stage_id = ?";
            $params[] = $filterStage;
        }

        if ($filterStatus > 0) {
            $where[] = "i.hold_status_id = ?";
            $params[] = $filterStatus;
        }

        $whereClause = implode(' AND ', $where);

        // Fetch inventory data
        $stmt = self::query(
            "SELECT 
                i.inventory_id,
                i.quantity,
                b.batch_id,
                b.internal_batch_code,
                b.supplier_useby_1,
                b.receipt_date,
                m.material_id,
                m.code as material_code,
                m.description as material_description,
                m.base_uom,
                s.supplier_id,
                s.supplier_name,
                st.stage_id,
                st.stage_name,
                hs.hold_status_id,
                hs.status_name,
                hs.is_available,
                hs.status_code,
                DATEDIFF(CURDATE(), DATE(b.receipt_date)) as age_days
             FROM inventory i
             JOIN batch b ON i.batch_id = b.batch_id
             JOIN material m ON b.material_id = m.material_id
             LEFT JOIN supplier s ON b.supplier_id = s.supplier_id
             JOIN stage st ON i.stage_id = st.stage_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE {$whereClause}
             ORDER BY b.receipt_date DESC, m.code ASC",
            $params
        );

        return $stmt->fetchAll();
    }

    /**
     * Get inventory summary statistics
     * Returns counts of total batches, available batches, on-hold batches, and active stages
     * 
     * @return array
     */
    public static function getInventorySummary(): array
    {
        // Get summary statistics
        $stmt = static::query(
            "SELECT 
                COUNT(DISTINCT i.inventory_id) as total_batches,
                COUNT(DISTINCT CASE WHEN hs.is_available = 1 THEN i.inventory_id END) as available_batches,
                COUNT(DISTINCT CASE WHEN hs.status_code = 'ON_HOLD' THEN i.inventory_id END) as onhold_batches,
                COUNT(DISTINCT i.stage_id) as stages_active
             FROM inventory i
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 AND hs.status_code != 'REJECTED'"
        );

        return $stmt->fetch();
    }

    public static function getPendingQAItems(): array
    {
        $stmt = static::query(
            "SELECT 
                b.internal_batch_code,
                m.code as material_code,
                m.description as material_description,
                i.quantity,
                m.base_uom,
                st.stage_name,
                DATEDIFF(CURDATE(), DATE(i.created_at)) as days_on_hold
             FROM inventory i
             JOIN batch b ON i.batch_id = b.batch_id
             JOIN material m ON b.material_id = m.material_id
             JOIN stage st ON i.stage_id = st.stage_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code = 'ON_HOLD'
             ORDER BY i.created_at ASC
             LIMIT 5"
        );

        return $stmt->fetchAll();
    }





    public static function getInventoryRecord(int $inventoryID): array
    {
        $stmt = static::query(
            "SELECT 
                i.*,
                b.internal_batch_code,
                b.material_id,
                m.code as material_code,
                m.base_uom,
                st.stage_name,
                hs.status_name,
                hs.status_code
             FROM inventory i
             JOIN batch b ON i.batch_id = b.batch_id
             JOIN material m ON b.material_id = m.material_id
             JOIN stage st ON i.stage_id = st.stage_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.inventory_id = ?",
            [$inventoryID]
        );

        return $stmt->fetch();
    }

    public static function getRejectedStock(): array
    {
        // Get rejected inventory
        $stmt = static::query(
            "SELECT 
                    i.inventory_id,
                    i.quantity,
                    b.batch_id,
                    b.internal_batch_code,
                    b.supplier_useby_1,
                    b.receipt_date,
                    m.material_id,
                    m.code as material_code,
                    m.description as material_description,
                    m.base_uom,
                    s.supplier_name,
                    st.stage_name,
                    DATEDIFF(CURDATE(), DATE(b.receipt_date)) as age_days,
                    t.notes as rejection_reason,
                    t.created_at as rejected_date,
                    u.username as rejected_by,
                    u.first_name,
                    u.last_name
                 FROM inventory i
                 JOIN batch b ON i.batch_id = b.batch_id
                 JOIN material m ON b.material_id = m.material_id
                 LEFT JOIN supplier s ON b.supplier_id = s.supplier_id
                 JOIN stage st ON i.stage_id = st.stage_id
                 JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
                 LEFT JOIN (
                    SELECT batch_id, notes, created_at, user_id,
                           ROW_NUMBER() OVER (PARTITION BY batch_id ORDER BY created_at DESC) as rn
                    FROM transactions
                    WHERE transaction_type = 'REJECT'
                 ) t ON b.batch_id = t.batch_id AND t.rn = 1
                 LEFT JOIN users u ON t.user_id = u.user_id
                 WHERE hs.status_code = 'REJECTED' AND i.quantity > 0
                 ORDER BY t.created_at DESC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Get summary statistics of rejected stock items
     *
     * @return array
     */
    public static function getRejectedStockSummary(): array
    {
        $stmt = static::query(
            "SELECT 
                    COUNT(DISTINCT i.inventory_id) as rejected_batches,
                    SUM(i.quantity) as total_quantity,
                    COUNT(DISTINCT b.material_id) as materials_affected
                 FROM inventory i
                 JOIN batch b ON i.batch_id = b.batch_id
                 JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
                 WHERE hs.status_code = 'REJECTED' AND i.quantity > 0"
        );

        return $stmt->fetch();
    }

    /**
     * Get inventory breakdown by status
     *
     * @param integer $batchId
     * @return array
     */
    public static function getInventoryBreakdown(int $batchId): array
    {
        $stmt = static::query(
            "SELECT 
                    i.inventory_id,
                    i.quantity,
                    st.stage_name,
                    hs.status_name,
                    hs.is_available,
                    i.created_at,
                    i.updated_at
                 FROM inventory i
                 JOIN stage st ON i.stage_id = st.stage_id
                 JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
                 WHERE i.batch_id = ? AND i.quantity > 0
                 ORDER BY st.stage_id, hs.hold_status_id",
            [$batchId]
        );

        return $stmt->fetchAll();
    }

    /**
     * Reduce quantity from original inventory record
     *
     * @param float $qty
     * @param integer $inventoryId
     * @return void
     */
    public static function reduceInventoryQty(float $qty, int $inventoryId): void
    {
        static::query(
            "UPDATE `inventory` 
                 SET `quantity` = `quantity` - ?,
                     `updated_at` = NOW()
                 WHERE `inventory_id` = ?",
            [$qty, $inventoryId]
        );
    }

    /**
     * Checks for existing batch record with same status/stage for partial quantity update
     *
     * @param integer $batchId
     * @param integer $stageId
     * @param integer $newStatusId
     * @param integer $inventoryId
     * @return array
     */
    public static function checkForExistingBatch(int $batchId, int $stageId, int $newStatusId, int $inventoryId): array|false
    {
        $stmt = static::query(
            "SELECT `inventory_id`, `quantity` 
                 FROM `inventory` 
                 WHERE `batch_id` = ? 
                   AND `stage_id` = ? 
                   AND `hold_status_id` = ?
                   AND `inventory_id` != ?
                 LIMIT 1",
            [
                $batchId,
                $stageId,
                $newStatusId,
                $inventoryId
            ]
        );

        return $stmt->fetch();
    }

    /**
     * Merges the partial quantity update on batch record with existing status
     *
     * @param float $qty
     * @param integer $inventoryId
     * @return void
     */
    public static function mergeExistingBatchRecord(float $qty, int $inventoryId): void
    {
        static::query(
            "UPDATE `inventory` 
                     SET `quantity` = `quantity` + ?,
                         `updated_at` = NOW()
                     WHERE `inventory_id` = ?",
            [$qty, $inventoryId]
        );
    }

    /**
     * Creates a new batch record for partial quantity status update
     *
     * @param integer $batchId
     * @param integer $stageId
     * @param integer $newStatusId
     * @param float $qty
     * @return void
     */
    public static function createNewBatchRecord($batchId, $stageId, $newStatusId, $qty): void
    {
        static::query(
            "INSERT INTO `inventory` (
                        `batch_id`,
                        `stage_id`,
                        `hold_status_id`,
                        `quantity`
                     ) VALUES (?, ?, ?, ?)",
            [
                $batchId,
                $stageId,
                $newStatusId,
                $qty
            ]
        );
    }

    public static function deleteEmptyInventoryRecord($inventoryId): void
    {
        static::query(
            "DELETE FROM `inventory` WHERE `inventory_id` = ?",
            [$inventoryId]
        );
    }

    public static function updateInventoryHoldStatus(int $newStatusId, int $inventoryId): void
    {
        static::query(
            "UPDATE `inventory` 
                     SET `hold_status_id` = ?,
                         `updated_at` = NOW()
                     WHERE `inventory_id` = ?",
            [$newStatusId, $inventoryId]
        );
    }

    /**
     * Retruns the total number of material with on-hold status
     *
     * @return integer
     */
    public static function getOnHoldInventoryCount(): int
    {
        $stmt = static::query(
            "SELECT COUNT(DISTINCT i.inventory_id) as count
             FROM inventory i
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code = 'ON_HOLD'"
        );

        return $stmt->fetch()['count'] ?? 0;
    }

    /**
     * Return total number of materials rejected within the last 7 days
     *
     * @return integer
     */
    public static function getRecentRejectionCount(): int
    {
        $stmt = static::query(
            "SELECT COUNT(DISTINCT i.inventory_id) as count
             FROM inventory i
             JOIN batch b ON i.batch_id = b.batch_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code = 'REJECTED'
               AND DATE(b.receipt_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );

        return $stmt->fetch()['count'] ?? 0;
    }
}
