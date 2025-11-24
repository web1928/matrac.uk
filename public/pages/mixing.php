<?php
/**
 * Mixing Page
 * Recipe selection and ingredient weighing
 * PLACEHOLDER - Full functionality to be implemented
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

if (!hasRole(['admin', 'mixer', 'manager'])) {
    header('Location: /DEV_matrac.uk/public/dashboard.php');
    exit;
}

$pageTitle = 'Mixing';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/DEV_matrac.uk/public/dashboard.php'],
    ['label' => 'Mixing', 'url' => null]
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content">
        <h1 class="page-title">Mixing</h1>
        
        <div class="alert alert--info">
            <div class="alert__message">
                This page is under development. The Mixing interface will allow you to:
                select recipes, weigh ingredients, track batch consumption, and manage active mixes.
            </div>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <div class="card__header">
                <h3 class="card__title">Start New Mix</h3>
            </div>
            <div class="card__body">
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Interface will include: Recipe selection, batch size selection, ingredient list with batch assignment, 
                    and mix start/complete workflows.
                </p>
                <button class="btn btn--primary" disabled>
                    Coming Soon
                </button>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
