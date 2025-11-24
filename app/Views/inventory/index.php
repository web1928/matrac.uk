<?php
// Set page data
$pageTitle = 'Inventory';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Inventory', 'url' => null]
];
$additionalScripts = ['js/pages/inventory.js'];

// Start output buffering for layout
ob_start();
?>

<h1 class="page-title">Inventory</h1>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">
                <?= h($summary['total_batches'] ?? 0) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">Total Batches</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">
                <?= h($summary['available_batches'] ?? 0) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">Available</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">
                <?= h($summary['onhold_batches'] ?? 0) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">On Hold</div>
        </div>
    </div>

    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">
                <?= h($summary['stages_active'] ?? 0) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">Stages Active</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card__body">
        <form method="GET" action="<?= url('/inventory') ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-material" class="form-label">Material</label>
                    <input
                        type="text"
                        id="filter-material"
                        name="material"
                        class="form-input"
                        placeholder="Search material..."
                        value="<?= h($filterMaterial) ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
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

                <div class="form-group" style="margin-bottom: 0;">
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
<div class="card" style="margin-top: 2rem;">
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

<!-- QA Action Modal -->
<div id="qa-action-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 8px; width: 500px; margin: 2rem;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;" id="qa-modal-title">QA Action</h3>
            <button onclick="document.getElementById('qa-action-modal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-primary);">&times;</button>
        </div>
        <div style="padding: 1.5rem;">
            <form id="qa-action-form">
                <input type="hidden" name="csrf_token" value="<?= h(generateCsrfToken()) ?>">
                <input type="hidden" id="qa-inventory-id" name="inventory_id">
                <input type="hidden" id="qa-action-type" name="action_type">

                <div id="qa-batch-info" style="background: var(--bg-secondary); padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
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
                    <button type="button" class="btn btn--secondary" onclick="document.getElementById('qa-action-modal').style.display='none'">
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