<?php

namespace App\Controllers;

use Core\Controller;

class RejectedStockController extends Controller
{
    /**
     * Show rejected stock page
     */
    public function index()
    {
        return $this->view('rejected-stock.index');
    }

    /**
     * Get rejected stock data (API endpoint)
     */
    public function getRejectedStock()
    {
        try {
            // Get rejected inventory
            $stmt = executeQuery(
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

            $rejectedStock = $stmt->fetchAll();

            // Get summary statistics
            $summaryStmt = executeQuery(
                "SELECT 
                    COUNT(DISTINCT i.inventory_id) as rejected_batches,
                    SUM(i.quantity) as total_quantity,
                    COUNT(DISTINCT b.material_id) as materials_affected
                 FROM inventory i
                 JOIN batch b ON i.batch_id = b.batch_id
                 JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
                 WHERE hs.status_code = 'REJECTED' AND i.quantity > 0"
            );

            $summary = $summaryStmt->fetch();

            return $this->json([
                'success' => true,
                'rejected_stock' => $rejectedStock,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            error_log("Get rejected stock error: " . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => 'Failed to fetch rejected stock'
            ], 500);
        }
    }

    /**
     * Get batch details (API endpoint)
     */
    public function getBatchDetails()
    {
        try {
            $batchId = $this->request->input('batch_id');

            if (!$batchId || $batchId <= 0) {
                throw new \Exception('Invalid batch ID');
            }

            // Get batch details
            $batchStmt = executeQuery(
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
                [$batchId]
            );

            $batch = $batchStmt->fetch();

            if (!$batch) {
                throw new \Exception('Batch not found');
            }

            // Get inventory breakdown by status
            $inventoryStmt = executeQuery(
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

            $inventory = $inventoryStmt->fetchAll();

            // Get transaction history
            $transactionsStmt = executeQuery(
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

            $transactions = $transactionsStmt->fetchAll();

            return $this->json([
                'success' => true,
                'batch' => $batch,
                'inventory' => $inventory,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            error_log("Get batch details error: " . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
