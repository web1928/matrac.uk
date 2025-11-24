<?php
/**
 * Header Include
 * Top bar with logo and breadcrumb navigation
 * 
 * Usage:
 * $breadcrumbs = [
 *     ['label' => 'Dashboard', 'url' => '/dashboard.php'],
 *     ['label' => 'Goods Receipt', 'url' => null] // null for active page
 * ];
 * include 'includes/header.php';
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/auth.php';
    initSecureSession();
}

// Default breadcrumbs if none provided
if (!isset($breadcrumbs)) {
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => null]
    ];
}

// Page title for <title> tag
$pageTitle = isset($pageTitle) ? h($pageTitle) . ' - MatraC' : 'MatraC - Material Traceability';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?php echo h(generateCsrfToken()); ?>">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/DEV_matrac.uk/assets/css/reset.css">
    <link rel="stylesheet" href="/DEV_matrac.uk/assets/css/layout.css">
    <link rel="stylesheet" href="/DEV_matrac.uk/assets/css/components.css">
    <link rel="stylesheet" href="/DEV_matrac.uk/assets/css/forms.css">
    <link rel="stylesheet" href="/DEV_matrac.uk/assets/css/tables.css">
    
    <!-- Favicon (placeholder) -->
    <link rel="icon" type="image/x-icon" href="/DEV_matrac.uk/assets/favicon.ico">
</head>
<body>
    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar__logo">MatraC</div>
        
        <nav class="topbar__breadcrumb" aria-label="Breadcrumb">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <?php if ($index > 0): ?>
                    <span class="topbar__breadcrumb-separator">/</span>
                <?php endif; ?>
                
                <?php if ($crumb['url']): ?>
                    <a href="<?php echo h($crumb['url']); ?>" class="topbar__breadcrumb-item">
                        <?php echo h($crumb['label']); ?>
                    </a>
                <?php else: ?>
                    <span class="topbar__breadcrumb-item topbar__breadcrumb-item--active">
                        <?php echo h($crumb['label']); ?>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </header>
    
    <!-- Sidebar Menu Toggle Tab -->
    <button class="sidebar-tab" aria-label="Toggle menu">
        <span class="sidebar-tab__icon">☰</span>
    </button>
