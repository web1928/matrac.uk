<?php

namespace App\Controllers;

use Matrac\Framework\Controller;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Transaction;

class GoodsReceiptController extends Controller
{
    /**
     * Show goods receipt form
     */
    public function index()
    {
        return $this->view('goods-receipt.index');
    }

    /**
     * Process goods receipt
     */
    public function store()
    {
        try {
            // Get input
            $materialId = $this->request->input('material_id');
            $quantity = $this->request->input('quantity');
            $supplierId = $this->request->input('supplier_id');
            $supplierUseby1 = $this->request->input('supplier_useby_1');
            $supplierBatch1 = $this->request->input('supplier_batch_code_1');

            // Validate required fields
            if (!$materialId || $materialId <= 0) {
                throw new \Exception('Invalid material selected');
            }

            if (!$quantity || $quantity <= 0) {
                throw new \Exception('Quantity must be greater than 0');
            }

            if (!$supplierId || $supplierId <= 0) {
                throw new \Exception('Invalid supplier selected');
            }

            if (empty($supplierUseby1)) {
                throw new \Exception('Supplier use by date is required');
            }

            if (empty($supplierBatch1)) {
                throw new \Exception('Supplier batch code is required');
            }

            // Get material for UOM
            $material = Material::findById($materialId);
            if (!$material) {
                throw new \Exception('Material not found');
            }

            // Get current user
            $user = getCurrentUser();
            $userId = $user['user_id'];

            // Begin transaction
            beginTransaction();

            // Create batch
            $batch = Batch::createFromReceipt([
                'material_id' => $materialId,
                'supplier_id' => $supplierId,
                'quantity' => $quantity,
                'uom' => $material['base_uom'],
                'supplier_useby_1' => $supplierUseby1,
                'supplier_batch_code_1' => $supplierBatch1,
                'supplier_useby_2' => $this->request->input('supplier_useby_2'),
                'supplier_batch_code_2' => $this->request->input('supplier_batch_code_2'),
                'supplier_useby_3' => $this->request->input('supplier_useby_3'),
                'supplier_batch_code_3' => $this->request->input('supplier_batch_code_3'),
                'supplier_useby_4' => $this->request->input('supplier_useby_4'),
                'supplier_batch_code_4' => $this->request->input('supplier_batch_code_4'),
                'po_number' => $this->request->input('po_number'),
                'haulier_name' => $this->request->input('haulier_name'),
                'delivery_note_ref' => $this->request->input('delivery_note_ref'),
                'silo_no' => $this->request->input('silo_no'),
                'coc_coa_attached' => $this->request->input('coc_coa_attached'),
                'rma_sheet_completed' => $this->request->input('rma_sheet_completed'),
                'matches_delivery_note' => $this->request->input('matches_delivery_note'),
                'bookin_confirmation_no' => $this->request->input('bookin_confirmation_no'),
                'receipt_comments' => $this->request->input('receipt_comments'),
            ], $userId);

            // Create inventory record
            Inventory::createFromReceipt($batch['batch_id'], $quantity);

            // Log transaction
            Transaction::logGoodsReceipt($batch['batch_id'], $quantity, $userId);

            // Commit transaction
            commitTransaction();

            // Return success
            return $this->json([
                'success' => true,
                'batch_code' => $batch['batch_code'],
                'batch_id' => $batch['batch_id']
            ]);
        } catch (\Exception $e) {
            // Rollback on error
            rollbackTransaction();

            // Log error
            error_log("Goods receipt error: " . $e->getMessage());

            // Return error
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Search materials (API endpoint)
     */
    public function searchMaterials()
    {
        $query = $this->request->input('q', '');
        $results = Material::search($query);

        return $this->json($results);
    }

    /**
     * Search suppliers (API endpoint)
     */
    public function searchSuppliers()
    {
        $query = $this->request->input('q', '');
        $results = Supplier::search($query);

        return $this->json($results);
    }

    /**
     * Get recent receipts (API endpoint)
     */
    public function recentReceipts()
    {
        $receipts = Batch::getTodayReceipts();

        return $this->json([
            'success' => true,
            'receipts' => $receipts
        ]);
    }
}
