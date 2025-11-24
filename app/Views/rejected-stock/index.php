<?php
// Set page data
$pageTitle = 'Rejected Stock';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Inventory', 'url' => '/inventory'],
    ['label' => 'Rejected Stock', 'url' => null]
];
$additionalScripts = ['js/pages/rejected-stock.js'];

// Start output buffering for layout
ob_start();
?>

<h1 class="page-title">Rejected Stock</h1>

<div class="alert alert--warning">
    <div class="alert__title">Rejected Inventory</div>
    <div class="alert__message">
        This view shows all inventory that has been permanently rejected by QA.
        Rejected stock cannot be used in production and should be disposed of appropriately.
    </div>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div id="rejected-batches" style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">-</div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">Rejected Batches</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div id="rejected-quantity" style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">-</div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">Total Quantity</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div id="rejected-materials" style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">-</div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">Materials Affected</div>
        </div>
    </div>
</div>

<!-- Rejected Stock Table -->
<div class="card">
    <div class="card__header">
        <h3 class="card__title">Rejected Inventory</h3>
    </div>
    <div class="card__body">
        <div id="rejected-container">
            <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                Loading rejected stock...
            </p>
        </div>
    </div>
</div>

<!-- Batch Detail Modal -->
<div id="batch-detail-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 8px; max-width: 800px; max-height: 90vh; overflow-y: auto; margin: 2rem;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Batch Details</h3>
            <button onclick="document.getElementById('batch-detail-modal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-primary);">&times;</button>
        </div>
        <div id="batch-detail-content" style="padding: 1.5rem;">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<?php
// Get buffered content
$content = ob_get_clean();

// Include layout
include __DIR__ . '/../layouts/main.php';
?>