<?php

namespace App\Controllers;

use Matrac\Framework\Controller;

class InventoryController extends Controller
{
    /**
     * Show inventory page with data
     */
    public function index()
    {
        // Get filter parameters from GET
        $filterMaterial = $this->request->input('material', '');
        $filterStage = $this->request->input('stage', 0);
        $filterStatus = $this->request->input('status', 0);

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

        if (!empty($filterMaterial)) {
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


        $inventory = $stmt->fetchAll();

        // Get summary statistics
        $summaryStmt = executeQuery(
            "SELECT 
                COUNT(DISTINCT i.inventory_id) as total_batches,
                COUNT(DISTINCT CASE WHEN hs.is_available = 1 THEN i.inventory_id END) as available_batches,
                COUNT(DISTINCT CASE WHEN hs.status_code = 'ON_HOLD' THEN i.inventory_id END) as onhold_batches,
                COUNT(DISTINCT i.stage_id) as stages_active
             FROM inventory i
             JOIN hold_status hs ON i.hold_status_id = hs.hold_status_id
             WHERE i.quantity > 0 AND hs.status_code != 'REJECTED'"
        );

        $summary = $summaryStmt->fetch();

        // Check if user can perform QA actions
        $user = getCurrentUser();
        $canPerformQAActions = in_array($user['role'], ['admin', 'qa', 'manager']);

        // Pass data to view
        return $this->view('inventory.index', [
            'inventory' => $inventory,
            'summary' => $summary,
            'canPerformQAActions' => $canPerformQAActions,
            'filterMaterial' => $filterMaterial,
            'filterStage' => $filterStage,
            'filterStatus' => $filterStatus,
        ]);
    }

    /**
     * Update hold status (QA actions)
     */
    public function updateHoldStatus()
    {
        try {
            // Get parameters
            $inventoryId = $this->request->input('inventory_id');
            $actionType = $this->request->input('action_type'); // 'hold', 'release', 'reject'
            $quantity = $this->request->input('quantity');
            $notes = $this->request->input('notes');

            // Validation
            if (!$inventoryId || $inventoryId <= 0) {
                throw new \Exception('Invalid inventory ID');
            }

            if (!in_array($actionType, ['hold', 'release', 'reject'])) {
                throw new \Exception('Invalid action type');
            }

            if (!$quantity || $quantity <= 0) {
                throw new \Exception('Quantity must be greater than 0');
            }

            // Get current inventory record
            $inventoryStmt = executeQuery(
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
                [$inventoryId]
            );

            $inventory = $inventoryStmt->fetch();

            if (!$inventory) {
                throw new \Exception('Inventory record not found');
            }

            // Validate quantity doesn't exceed available
            if ($quantity > $inventory['quantity']) {
                throw new \Exception("Quantity exceeds available ({$inventory['quantity']} {$inventory['base_uom']})");
            }

            // Validate action is appropriate for current status
            if ($actionType === 'release' && $inventory['status_code'] !== 'ON_HOLD') {
                throw new \Exception('Can only release items that are on hold');
            }

            if ($actionType === 'hold' && $inventory['status_code'] !== 'AVAILABLE') {
                throw new \Exception('Can only hold items that are available');
            }

            if ($actionType === 'reject' && $inventory['status_code'] === 'REJECTED') {
                throw new \Exception('Item is already rejected');
            }

            // Determine new status
            $newStatusMap = [
                'hold' => 2,    // ON_HOLD
                'release' => 1, // AVAILABLE
                'reject' => 3   // REJECTED
            ];
            $newStatusId = $newStatusMap[$actionType];

            // Determine transaction type
            $transactionTypeMap = [
                'hold' => 'HOLD',
                'release' => 'RELEASE',
                'reject' => 'REJECT'
            ];
            $transactionType = $transactionTypeMap[$actionType];

            // Get current user
            $user = getCurrentUser();
            $userId = $user['user_id'];

            // Begin transaction
            beginTransaction();

            if ($quantity < $inventory['quantity']) {
                // PARTIAL quantity action

                // Reduce quantity from original inventory record
                executeQuery(
                    "UPDATE inventory 
                 SET quantity = quantity - ?,
                     updated_at = NOW()
                 WHERE inventory_id = ?",
                    [$quantity, $inventoryId]
                );

                // Check if there's an existing inventory record with same batch/stage/status
                $existingStmt = executeQuery(
                    "SELECT inventory_id, quantity 
                 FROM inventory 
                 WHERE batch_id = ? 
                   AND stage_id = ? 
                   AND hold_status_id = ?
                   AND inventory_id != ?
                 LIMIT 1",
                    [
                        $inventory['batch_id'],
                        $inventory['stage_id'],
                        $newStatusId,
                        $inventoryId
                    ]
                );

                $existing = $existingStmt->fetch();

                if ($existing) {
                    // MERGE: Add to existing record
                    executeQuery(
                        "UPDATE inventory 
                     SET quantity = quantity + ?,
                         updated_at = NOW()
                     WHERE inventory_id = ?",
                        [$quantity, $existing['inventory_id']]
                    );
                } else {
                    // CREATE: New inventory record with new status
                    executeQuery(
                        "INSERT INTO inventory (
                        batch_id,
                        stage_id,
                        hold_status_id,
                        quantity
                     ) VALUES (?, ?, ?, ?)",
                        [
                            $inventory['batch_id'],
                            $inventory['stage_id'],
                            $newStatusId,
                            $quantity
                        ]
                    );
                }
            } else {
                // FULL quantity action

                // Check if there's another inventory record to merge into
                $existingStmt = executeQuery(
                    "SELECT inventory_id, quantity 
                 FROM inventory 
                 WHERE batch_id = ? 
                   AND stage_id = ? 
                   AND hold_status_id = ?
                   AND inventory_id != ?
                 LIMIT 1",
                    [
                        $inventory['batch_id'],
                        $inventory['stage_id'],
                        $newStatusId,
                        $inventoryId
                    ]
                );

                $existing = $existingStmt->fetch();

                if ($existing) {
                    // MERGE: Add quantity to existing and delete this record
                    executeQuery(
                        "UPDATE inventory 
                     SET quantity = quantity + ?,
                         updated_at = NOW()
                     WHERE inventory_id = ?",
                        [$quantity, $existing['inventory_id']]
                    );

                    // Delete the now-empty original record
                    executeQuery(
                        "DELETE FROM inventory WHERE inventory_id = ?",
                        [$inventoryId]
                    );
                } else {
                    // UPDATE: Just change status
                    executeQuery(
                        "UPDATE inventory 
                     SET hold_status_id = ?,
                         updated_at = NOW()
                     WHERE inventory_id = ?",
                        [$newStatusId, $inventoryId]
                    );
                }
            }

            // Log transaction
            executeQuery(
                "INSERT INTO transactions (
                transaction_type,
                batch_id,
                from_stage_id,
                to_stage_id,
                quantity,
                user_id,
                notes
             ) VALUES (?, ?, ?, NULL, ?, ?, ?)",
                [
                    $transactionType,
                    $inventory['batch_id'],
                    $inventory['stage_id'],
                    $quantity,
                    $userId,
                    $notes ?: "QA action: {$actionType}"
                ]
            );

            // Commit transaction
            commitTransaction();

            return $this->json([
                'success' => true,
                'message' => "Successfully {$actionType}ed {$quantity} {$inventory['base_uom']} of batch {$inventory['internal_batch_code']}"
            ]);
        } catch (\Exception $e) {
            rollbackTransaction();

            error_log("Hold status update error: " . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
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
