<?php

declare(strict_types=1);

namespace App\Controllers;

use Matrac\Framework\Controller;
use App\Models\Auth;
use App\Models\Batch;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Inventory;

class DashboardController extends Controller
{
    private array $user = [];
    private array $stats = [];


    /**
     * Return the dahsboard view, displaying stats
     *
     * @return null
     */
    public function index(): void
    {
        $this->user = Auth::getCurrentUser($_SESSION['user']);

        $this->stats = [

            'today_receipts' => Batch::getTodayReceiptCount(),
            'active_batches' => Batch::getActiveBatchCount(),
            'on_hold' => Inventory::getOnHoldInventoryCount(),
            'active_materials' => Material::getActiveMaterialCount(),
            'active_suppliers' => Supplier::getActiveSupplierCount(),
            'recent_rejects' => Inventory::getRecentRejectionCount(),
        ];


        $this->view('dashboard.index', [
            'user' => $this->user,
            'stats' => $this->stats,
            'recentReceipts' => Batch::getRecentReceipts(),
            'pendingQA' => Inventory::getPendingQAItems(),
        ]);
    }
}
