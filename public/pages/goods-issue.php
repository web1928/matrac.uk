<?php
/**
 * Goods Issue Page
 * Form for issuing materials to production/mixing
 * PLACEHOLDER - Full functionality to be implemented
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

if (!hasRole(['admin', 'goods_issuer', 'manager'])) {
    header('Location: /DEV_matrac.uk/public/dashboard.php');
    exit;
}

$pageTitle = 'Goods Issue';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/DEV_matrac.uk/public/dashboard.php'],
    ['label' => 'Goods Issue', 'url' => null]
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content">
        <h1 class="page-title">Goods Issue</h1>
        
        <div class="alert alert--info">
            <div class="alert__message">
                This page is under development. The Goods Issue form will allow you to:
                issue materials to production, capture sieving data (where applicable), and update inventory.
            </div>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <div class="card__header">
                <h3 class="card__title">Issue Materials</h3>
            </div>
            <div class="card__body">
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Form fields will include: Batch selection, Quantity, Sieving data, Bin reference, Line allocation, etc.
                </p>
                <button class="btn btn--primary" disabled>
                    Coming Soon
                </button>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
