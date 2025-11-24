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
<div class="stats-grid stats-grid--compact">
    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent" id="rejected-batches">-</div>
            <div class="stats-card__label stats-card__label--sm">Rejected Batches</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent" id="rejected-quantity">-</div>
            <div class="stats-card__label stats-card__label--sm">Total Quantity</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent" id="rejected-materials">-</div>
            <div class="stats-card__label stats-card__label--sm">Materials Affected</div>
        </div>
    </div>
</div>

<!-- Rejected Stock Table -->
<div class="card" style="margin-top: 2rem;">
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
<div id="batch-detail-modal" class="modal">
    <div class="modal__dialog modal__dialog--lg">
        <div class="modal__header">
            <h3 class="modal__title">Batch Details</h3>
            <button class="modal__close">&times;</button>
        </div>
        <div id="batch-detail-content" class="modal__body">
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