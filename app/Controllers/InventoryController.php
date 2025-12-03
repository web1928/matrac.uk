<?php

declare(strict_types=1);

namespace App\Controllers;

use Matrac\Framework\Controller;
use App\Models\Auth;
use App\Models\Batch;
use App\Models\HoldStatus;
use App\Models\Inventory;
use App\Models\Transaction;

class InventoryController extends Controller
{
    /**
     * Show inventory page with data
     */
    public function index(): void
    {
        // Get filter parameters from GET
        $filterMaterial = (string) $this->request->input('material', '');
        $filterStage = (int) $this->request->input('stage', 0);
        $filterStatus = (int) $this->request->input('status', 0);

        $inventoryFull = Inventory::getAvailableInventory($filterMaterial, $filterStage, $filterStatus);

        $inventorySummary = Inventory::getInventorySummary();

        // Check if user can perform QA actions
        $user = Auth::getCurrentUser($_SESSION['user']);
        $canPerformQAActions = in_array($user['role'], ['admin', 'qa', 'manager']);

        // Pass data to view
        $this->view('inventory.index', [
            'inventory' => $inventoryFull,
            'summary' => $inventorySummary,
            'canPerformQAActions' => $canPerformQAActions,
            'filterMaterial' => $filterMaterial,
            'filterStage' => $filterStage,
            'filterStatus' => $filterStatus,
        ]);
    }

    /**
     * Update hold status (QA actions)
     */
    public function updateHoldStatus(): string
    {
        try {
            // Get parameters
            $inventoryId = (int)$this->request->input('inventory_id');
            $actionType = (string)$this->request->input('action_type'); // 'hold', 'release', 'reject'
            $quantity = (float)$this->request->input('quantity');
            $notes = (string) $this->request->input('notes');

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
            $inventory = Inventory::getInventoryRecord($inventoryId);

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
            $newStatusId = HoldStatus::getStatusIdFromAction($actionType);

            // Determine transaction type
            $transactionType = HoldStatus::getTransactionType($actionType);

            // Get current user
            $user = Auth::getCurrentUser($_SESSION['user']);
            $userId = $user['user_id'];

            // Begin transaction
            Inventory::beginTransaction();

            if ($quantity < $inventory['quantity']) {
                // PARTIAL quantity action

                // Reduce quantity from original inventory record
                Inventory::reduceInventoryQty($quantity, $inventoryId);

                // Check if there's an existing inventory record with same batch/stage/status
                $existing = Inventory::checkForExistingBatch(
                    $inventory['batch_id'],
                    $inventory['stage_id'],
                    $newStatusId,
                    $inventoryId
                );


                if ($existing) {

                    // MERGE: Add to existing record
                    Inventory::mergeExistingBatchRecord($quantity, $existing['inventory_id']);
                } else {

                    // CREATE: New inventory record with new status
                    Inventory::createNewBatchRecord($inventory['batch_id'], $inventory['stage_id'], $newStatusId, $quantity);
                }
            } else {
                // FULL quantity action

                // Check if there's another inventory record to merge into
                $existing = Inventory::checkForExistingBatch(
                    $inventory['batch_id'],
                    $inventory['stage_id'],
                    $newStatusId,
                    $inventoryId
                );


                if ($existing) {
                    // MERGE: Add quantity to existing and delete this record

                    Inventory::mergeExistingBatchRecord($quantity, $existing['inventory_id']);

                    // Delete the now-empty original record
                    Inventory::deleteEmptyInventoryRecord($inventoryId);
                } else {
                    // UPDATE: Just change status
                    Inventory::updateInventoryHoldStatus($newStatusId, $inventoryId);
                }
            }

            // Log transaction
            Transaction::logTransaction(
                $transactionType,
                $inventory['batch_id'],
                $inventory['stage_id'],
                $quantity,
                $userId,
                $notes,
                $actionType
            );


            // Commit transaction
            Inventory::commitTransaction();

            return $this->json([
                'success' => true,
                'message' => "Successfully {$actionType}ed {$quantity} {$inventory['base_uom']} of batch {$inventory['internal_batch_code']}"
            ]);
        } catch (\Exception $e) {
            Inventory::rollbackTransaction();

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
    public function getBatchDetails(): string
    {
        $batchId = (int)$this->request->input('batch_id');

        if (!$batchId || $batchId <= 0) {
            throw new \Exception('Invalid batch ID');
        }

        $batch = Batch::getBatchDetails($batchId);

        if (!$batch) {
            throw new \Exception('Batch not found');
        }

        // Get inventory breakdown by status
        $inventory = Inventory::getInventoryBreakdown($batchId);

        // Get transaction history
        $transactions = Transaction::getTransactionHistory($batchId);

        return $this->json([
            'success' => true,
            'batch' => $batch,
            'inventory' => $inventory,
            'transactions' => $transactions
        ]);
    }
}
