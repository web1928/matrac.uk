<?php
// Set page data
$pageTitle = 'Dashboard';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => null]
];

// Start output buffering for layout
ob_start();
?>

<h1 class="page-title">Dashboard</h1>

<!-- Welcome Message -->
<div class="card">
    <div class="card__body">
        <h2 style="margin-bottom: 1rem;">Welcome back, <?= h($user['first_name']) ?>!</h2>
        <p style="color: var(--text-secondary);">
            You are logged in as <strong><?= h(ucwords(str_replace('_', ' ', $user['role']))) ?></strong>.
        </p>
    </div>
</div>

<!-- Statistics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
    <!-- Batches Received Today -->
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: #B45346; margin-bottom: 0.5rem;">
                <?= h($stats['today_receipts']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.938rem;">Batches Received Today</div>
        </div>
    </div>

    <!-- Active Batches -->
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: #B45346; margin-bottom: 0.5rem;">
                <?= h($stats['active_batches']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.938rem;">Active Batches</div>
        </div>
    </div>

    <!-- Materials on Hold -->
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: <?= $stats['on_hold'] > 0 ? '#B45346' : '#B45346' ?>; margin-bottom: 0.5rem;">
                <?= h($stats['on_hold']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.938rem;">Materials on Hold</div>
        </div>
    </div>

    <!-- Active Materials -->
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: #B45346; margin-bottom: 0.5rem;">
                <?= h($stats['active_materials']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.938rem;">Active Materials</div>
        </div>
    </div>

    <!-- Active Suppliers -->
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: #B45346; margin-bottom: 0.5rem;">
                <?= h($stats['active_suppliers']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.938rem;">Active Suppliers</div>
        </div>
    </div>

    <!-- Recent Rejects (Last 7 Days) -->
    <div class="card">
        <div class="card__body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: <?= $stats['recent_rejects'] > 0 ? '#333' : '#B45346' ?>; margin-bottom: 0.5rem;">
                <?= h($stats['recent_rejects']) ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.938rem;">Rejects (Last 7 Days)</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 2rem;">
    <div class="card__header">
        <h3 class="card__title">Quick Actions</h3>
    </div>
    <div class="card__body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <?php if (hasRole(['admin', 'goods_receptor', 'manager'])): ?>
                <a href="<?= url('/goods-receipt') ?>" class="btn btn--primary">
                    + New Goods Receipt
                </a>
            <?php endif; ?>

            <?php if (hasRole(['admin', 'goods_issuer', 'manager'])): ?>
                <a href="<?= url('/goods-issue') ?>" class="btn btn--primary">
                    + Issue Materials
                </a>
            <?php endif; ?>

            <?php if (hasRole(['admin', 'mixer', 'manager'])): ?>
                <a href="<?= url('/mixing') ?>" class="btn btn--primary">
                    + Start New Mix
                </a>
            <?php endif; ?>

            <a href="<?= url('/inventory') ?>" class="btn btn--secondary">
                View Inventory
            </a>

            <?php if (hasRole(['admin', 'qa', 'stock_manager', 'manager'])): ?>
                <a href="<?= url('/rejected-stock') ?>" class="btn btn--secondary">
                    View Rejected Stock
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Receipts -->
<?php if (!empty($recentReceipts)): ?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card__header">
            <h3 class="card__title">Recent Receipts (Today)</h3>
        </div>
        <div class="card__body">
            <table class="table table--striped">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Supplier</th>
                        <th>Time</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReceipts as $receipt): ?>
                        <tr>
                            <td><strong><?= h($receipt['internal_batch_code']) ?></strong></td>
                            <td><?= h($receipt['material_code']) ?> - <?= h($receipt['material_description']) ?></td>
                            <td><?= number_format($receipt['delivered_quantity'], 3) ?> <?= h($receipt['delivered_qty_uom']) ?></td>
                            <td><?= $receipt['supplier_name'] ? h($receipt['supplier_name']) : '-' ?></td>
                            <td><?= date('H:i', strtotime($receipt['receipt_date'])) ?></td>
                            <td><?= h($receipt['received_by']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Pending QA Items -->
<?php if (!empty($pendingQA) && hasRole(['admin', 'qa', 'manager'])): ?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card__header">
            <h3 class="card__title">Pending QA Items (On Hold)</h3>
        </div>
        <div class="card__body">
            <table class="table table--striped">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Material</th>
                        <th>Stage</th>
                        <th>Quantity</th>
                        <th>Days On Hold</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingQA as $item): ?>
                        <tr>
                            <td><strong><?= h($item['internal_batch_code']) ?></strong></td>
                            <td><?= h($item['material_code']) ?> - <?= h($item['material_description']) ?></td>
                            <td><?= h($item['stage_name']) ?></td>
                            <td><?= number_format($item['quantity'], 3) ?> <?= h($item['base_uom']) ?></td>
                            <td>
                                <span class="badge badge--<?= $item['days_on_hold'] > 3 ? 'error' : 'warning' ?>">
                                    <?= h($item['days_on_hold']) ?> day<?= $item['days_on_hold'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/inventory') ?>" class="table-action-btn">
                                    Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- No Recent Activity Message -->
<?php if (empty($recentReceipts) && empty($pendingQA)): ?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card__body">
            <div class="empty-state">
                <div class="empty-state__icon">📊</div>
                <div class="empty-state__title">No Recent Activity</div>
                <div class="empty-state__message">
                    No receipts or pending QA items today. Use the quick actions above to get started!
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Get buffered content
$content = ob_get_clean();

// Include layout
include __DIR__ . '/../layouts/main.php';
?>