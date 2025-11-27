<?php
// Set page data
$pageTitle = 'Inventory';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Inventory', 'url' => null]
];
$additionalScripts = ['js/pages/inventory.js'];
echo $a;

// Start output buffering for layout
ob_start();
?>

<h1 class="page-title">Inventory</h1>

<!-- Summary Cards -->
<div class="stats-grid stats-grid--compact">
    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent">
                <?= h($summary['total_batches'] ?? 0) ?>
            </div>
            <div class="stats-card__label stats-card__label--sm">Total Batches</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent">
                <?= h($summary['available_batches'] ?? 0) ?>
            </div>
            <div class="stats-card__label stats-card__label--sm">Available</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent">
                <?= h($summary['onhold_batches'] ?? 0) ?>
            </div>
            <div class="stats-card__label stats-card__label--sm">On Hold</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body stats-card__body">
            <div class="stats-card__value stats-card__value--md stats-card__value--accent">
                <?= h($summary['stages_active'] ?? 0) ?>
            </div>
            <div class="stats-card__label stats-card__label--sm">Stages Active</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card__body">
        <form method="GET" action="<?= url('/inventory') ?>">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="filter-material" class="form-label">Material</label>
                    <input
                        type="text"
                        id="filter-material"
                        name="material"
                        class="form-input"
                        placeholder="Search material..."
                        value="<?= h($filterMaterial) ?>">
                </div>

                <div class="form-group">
                    <label for="filter-stage" class="form-label">Stage</label>
                    <select id="filter-stage" name="stage" class="form-select">
                        <option value="">All Stages</option>
                        <option value="1" <?= $filterStage == 1 ? 'selected' : '' ?>>Goods Receipt</option>
                        <option value="2" <?= $filterStage == 2 ? 'selected' : '' ?>>Deboxing/Tempering</option>
                        <option value="3" <?= $filterStage == 3 ? 'selected' : '' ?>>Goods Issue</option>
                        <option value="4" <?= $filterStage == 4 ? 'selected' : '' ?>>Mixing</option>
                        <option value="5" <?= $filterStage == 5 ? 'selected' : '' ?>>Rework Available</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1" <?= $filterStatus == 1 ? 'selected' : '' ?>>Available</option>
                        <option value="2" <?= $filterStatus == 2 ? 'selected' : '' ?>>On Hold</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn--primary">
                        Apply Filters
                    </button>
                    <a href="<?= url('/inventory') ?>" class="btn btn--secondary">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Inventory Table (PHP-RENDERED!) -->
<div class="card mt-lg">
    <div class="card__header">
        <h3 class="card__title">Current Inventory</h3>
    </div>
    <div class="card__body">
        <?php if (empty($inventory)): ?>
            <div class="empty-state">
                <div class="empty-state__message">No inventory found matching your filters</div>
            </div>
        <?php else: ?>
            <table class="table table--striped">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Material</th>
                        <th>Stage</th>
                        <th>Status</th>
                        <th>Quantity</th>
                        <th>Supplier</th>
                        <th>Use By</th>
                        <th>Age (Days)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><strong><?= h($item['internal_batch_code']) ?></strong></td>
                            <td><?= h($item['material_code']) ?> - <?= h($item['material_description']) ?></td>
                            <td><?= h($item['stage_name']) ?></td>
                            <td>
                                <span class="badge badge--<?= $item['is_available'] ? 'success' : 'warning' ?>">
                                    <?= h($item['status_name']) ?>
                                </span>
                            </td>
                            <td><?= number_format($item['quantity'], 3) ?> <?= h($item['base_uom']) ?></td>
                            <td><?= $item['supplier_name'] ? h($item['supplier_name']) : '-' ?></td>
                            <td><?= $item['supplier_useby_1'] ? date('d/m/Y', strtotime($item['supplier_useby_1'])) : '-' ?></td>
                            <td><?= h($item['age_days']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <button
                                        class="table-action-btn"
                                        onclick="viewBatchDetails(<?= $item['batch_id'] ?>)">
                                        View
                                    </button>

                                    <?php if ($canPerformQAActions): ?>
                                        <?php if ($item['status_code'] === 'AVAILABLE'): ?>
                                            <button
                                                class="table-action-btn"
                                                onclick="openQAModal(<?= $item['inventory_id'] ?>, 'hold', <?= $item['quantity'] ?>, '<?= h($item['base_uom']) ?>', '<?= h($item['internal_batch_code']) ?>', '<?= h($item['material_code']) ?>', '<?= h($item['status_name']) ?>')">
                                                Hold
                                            </button>
                                            <button
                                                class="table-action-btn"
                                                onclick="openQAModal(<?= $item['inventory_id'] ?>, 'reject', <?= $item['quantity'] ?>, '<?= h($item['base_uom']) ?>', '<?= h($item['internal_batch_code']) ?>', '<?= h($item['material_code']) ?>', '<?= h($item['status_name']) ?>')"
                                                style="border-color: #666;">
                                                Reject
                                            </button>
                                        <?php elseif ($item['status_code'] === 'ON_HOLD'): ?>
                                            <button
                                                class="table-action-btn"
                                                onclick="openQAModal(<?= $item['inventory_id'] ?>, 'release', <?= $item['quantity'] ?>, '<?= h($item['base_uom']) ?>', '<?= h($item['internal_batch_code']) ?>', '<?= h($item['material_code']) ?>', '<?= h($item['status_name']) ?>')">
                                                Release
                                            </button>
                                            <button
                                                class="table-action-btn"
                                                onclick="openQAModal(<?= $item['inventory_id'] ?>, 'reject', <?= $item['quantity'] ?>, '<?= h($item['base_uom']) ?>', '<?= h($item['internal_batch_code']) ?>', '<?= h($item['material_code']) ?>', '<?= h($item['status_name']) ?>')"
                                                style="border-color: #666;">
                                                Reject
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
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

<!-- QA Action Modal -->
<div id="qa-action-modal" class="modal">
    <div class="modal__dialog modal__dialog--fixed">
        <div class="modal__header">
            <h3 class="modal__title" id="qa-modal-title">QA Action</h3>
            <button class="modal__close">&times;</button>
        </div>
        <div class="modal__body">
            <form id="qa-action-form">
                <input type="hidden" name="csrf_token" value="<?= h(generateCsrfToken()) ?>">
                <input type="hidden" id="qa-inventory-id" name="inventory_id">
                <input type="hidden" id="qa-action-type" name="action_type">

                <div id="qa-batch-info" class="modal__info-box">
                    <!-- Batch info loaded dynamically -->
                </div>

                <div class="form-group">
                    <label for="qa-quantity" class="form-label form-label--required">Quantity</label>
                    <div class="input-group">
                        <input
                            type="number"
                            id="qa-quantity"
                            name="quantity"
                            class="form-input"
                            step="0.001"
                            min="0.001"
                            required>
                        <span class="input-group__suffix" id="qa-uom">KG</span>
                    </div>
                    <span class="form-help">Enter quantity (partial or full)</span>
                </div>

                <div class="form-group">
                    <label for="qa-notes" class="form-label">Reason / Notes</label>
                    <textarea
                        id="qa-notes"
                        name="notes"
                        class="form-textarea"
                        rows="3"
                        placeholder="Enter reason for this action..."></textarea>
                </div>

                <div class="form-actions form-actions--right">
                    <button type="button" class="btn btn--secondary" id="qa-cancel-btn">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn--primary" id="qa-submit-btn">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Get buffered content
$content = ob_get_clean();

// Include layout
include __DIR__ . '/../layouts/main.php';
?>