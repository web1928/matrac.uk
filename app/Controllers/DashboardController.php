<?php

namespace App\Controllers;

use Core\Controller;

class DashboardController extends Controller
{
    /**
     * Show dashboard with real statistics
     */
    public function index()
    {
        $user = getCurrentUser();

        // Get statistics
        $stats = $this->getDashboardStats();

        // Get recent receipts (today)
        $recentReceipts = $this->getRecentReceipts();

        // Get pending QA items (on hold)
        $pendingQA = $this->getPendingQAItems();

        return $this->view('dashboard.index', [
            'user' => $user,
            'stats' => $stats,
            'recentReceipts' => $recentReceipts,
            'pendingQA' => $pendingQA,
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        // Batches received today
        $todayReceiptsStmt = executeQuery(
            "SELECT COUNT(*) as count 
             FROM batch 
             WHERE DATE(receipt_date) = CURDATE()"
        );
        $todayReceipts = $todayReceiptsStmt->fetch()['count'] ?? 0;

        // Active batches (inventory > 0, not rejected)
        $activeBatchesStmt = executeQuery(
            "SELECT COUNT(DISTINCT b.batch_id) as count
             FROM batch b
             JOIN inventory i ON b.batch_id = i.batch_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code != 'REJECTED'"
        );
        $activeBatches = $activeBatchesStmt->fetch()['count'] ?? 0;

        // Materials on hold
        $onHoldStmt = executeQuery(
            "SELECT COUNT(DISTINCT i.inventory_id) as count
             FROM inventory i
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code = 'ON_HOLD'"
        );
        $onHold = $onHoldStmt->fetch()['count'] ?? 0;

        // Total active materials
        $activeMaterialsStmt = executeQuery(
            "SELECT COUNT(*) as count 
             FROM material 
             WHERE active = 1"
        );
        $activeMaterials = $activeMaterialsStmt->fetch()['count'] ?? 0;

        // Total active suppliers
        $activeSuppliersStmt = executeQuery(
            "SELECT COUNT(*) as count 
             FROM supplier 
             WHERE active = 1"
        );
        $activeSuppliers = $activeSuppliersStmt->fetch()['count'] ?? 0;

        // Rejected items (last 7 days)
        $recentRejectsStmt = executeQuery(
            "SELECT COUNT(DISTINCT i.inventory_id) as count
             FROM inventory i
             JOIN batch b ON i.batch_id = b.batch_id
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 
               AND hs.status_code = 'REJECTED'
               AND DATE(b.receipt_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );
        $recentRejects = $recentRejectsStmt->fetch()['count'] ?? 0;

        return [
            'today_receipts' => $todayReceipts,
            'active_batches' => $activeBatches,
            'on_hold' => $onHold,
            'active_materials' => $activeMaterials,
            'active_suppliers' => $activeSuppliers,
            'recent_rejects' => $recentRejects,
        ];
    }

    /**
     * Get recent receipts (today)
     */
    private function getRecentReceipts()
    {
        $stmt = executeQuery(
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

    /**
     * Get pending QA items (on hold)
     */
    private function getPendingQAItems()
    {
        $stmt = executeQuery(
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
}
