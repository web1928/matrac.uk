<?php

declare(strict_types=1);

namespace App\Controllers;

use Matrac\Framework\Controller;
use App\Models\Inventory;
use App\Models\Batch;
use App\Models\Transaction;


class RejectedStockController extends Controller
{

    private int $batchId = 0;

    /**
     * Show rejected stock page
     */
    public function index(): void
    {
        $this->view('rejected-stock.index');
    }

    /**
     * Get rejected stock data (API endpoint)
     */
    public function getRejectedStock(): string
    {
        return $this->json([
            'success' => true,
            'rejected_stock' => Inventory::getRejectedStock(),
            'summary' => Inventory::getrejectedStockSummary()
        ]);
    }

    /**
     * Get batch details (API endpoint)
     */
    public function getBatchDetails(): string
    {
        try {

            $this->batchId = (int) $this->request->input('batch_id');

            if (!$this->batchId || $this->batchId <= 0) {
                throw new \Exception('Invalid batch ID');
            }

            // get the Batch details of the rejected stock line
            $batch = Batch::getBatchDetails($this->batchId);

            if (!$batch) {
                throw new \Exception('Batch not found');
            }

            // Get the Status breakdown of the rejected stock batch
            $inventory = Inventory::getInventoryBreakdown($this->batchId);

            // dd($inventory);

            // Get transaction history
            $transactions = Transaction::getTransactionHistory($this->batchId);

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
