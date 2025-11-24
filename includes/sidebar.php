<?php
/**
 * Sidebar Navigation
 * Sliding menu with user info and navigation links
 * Menu items filtered by user role
 */

// Get current user
$currentUser = getCurrentUser();
$userRole = $currentUser['role'] ?? 'guest';
$firstName = $currentUser['first_name'] ?? 'Guest';
$lastName = $currentUser['last_name'] ?? 'User';
$fullName = trim($firstName . ' ' . $lastName);

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);

/**
 * Define menu items by role
 * Each item: ['label', 'url', 'roles']
 * roles: array of roles that can see this item, or ['all'] for everyone
 */
$menuItems = [
    [
        'label' => 'Dashboard',
        'url' => '/DEV_matrac.uk/public/dashboard.php',
        'roles' => ['all']
    ],
    [
        'label' => 'Goods Receipt',
        'url' => '/DEV_matrac.uk/public/pages/goods-receipt.php',
        'roles' => ['admin', 'goods_receptor', 'manager']
    ],
    [
        'label' => 'Deboxing/Tempering',
        'url' => '/DEV_matrac.uk/public/pages/deboxing.php',
        'roles' => ['admin', 'goods_issuer', 'manager']
    ],
    [
        'label' => 'Goods Issue',
        'url' => '/DEV_matrac.uk/public/pages/goods-issue.php',
        'roles' => ['admin', 'goods_issuer', 'manager']
    ],
    [
        'label' => 'Mixing',
        'url' => '/DEV_matrac.uk/public/pages/mixing.php',
        'roles' => ['admin', 'mixer', 'manager']
    ],
    [
        'label' => 'Active Mixes',
        'url' => '/DEV_matrac.uk/public/pages/active-mixes.php',
        'roles' => ['admin', 'mixer', 'manager']
    ],
    [
        'label' => 'Inventory',
        'url' => '/DEV_matrac.uk/public/pages/inventory.php',
        'roles' => ['all']  // All users can view inventory
    ],
    [
        'label' => 'Rejected Stock',
        'url' => '/DEV_matrac.uk/public/pages/rejected-stock.php',
        'roles' => ['admin', 'qa', 'stock_manager', 'manager']
    ],
    [
        'label' => 'Reports',
        'url' => '/DEV_matrac.uk/public/pages/reports.php',
        'roles' => ['admin', 'manager', 'qa']
    ],
    // Separator
    ['separator' => true],
    [
        'label' => 'Settings',
        'url' => '/DEV_matrac.uk/public/pages/settings.php',
        'roles' => ['all']
    ],
    [
        'label' => 'Help',
        'url' => '/DEV_matrac.uk/public/pages/help.php',
        'roles' => ['all']
    ],
];

/**
 * Check if user can see menu item
 */
function canSeeMenuItem($item, $userRole) {
    if (isset($item['separator'])) {
        return true;
    }
    
    if (in_array('all', $item['roles'])) {
        return true;
    }
    
    return in_array($userRole, $item['roles']);
}

/**
 * Check if menu item is active
 */
function isActiveMenuItem($itemUrl, $currentPage) {
    return basename($itemUrl) === $currentPage;
}
?>

<!-- Sidebar Backdrop -->
<div class="sidebar-backdrop"></div>

<!-- Sidebar Navigation -->
<aside class="sidebar">
    <!-- User Info Header -->
    <div class="sidebar__header">
        <div class="sidebar__user-name"><?php echo h($fullName); ?></div>
        <div class="sidebar__user-role"><?php echo h(ucwords(str_replace('_', ' ', $userRole))); ?></div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar__nav">
        <?php foreach ($menuItems as $item): ?>
            <?php if (!canSeeMenuItem($item, $userRole)) continue; ?>
            
            <?php if (isset($item['separator'])): ?>
                <div class="sidebar__nav-separator"></div>
            <?php else: ?>
                <a href="<?php echo h($item['url']); ?>" 
                   class="sidebar__nav-item <?php echo isActiveMenuItem($item['url'], $currentPage) ? 'sidebar__nav-item--active' : ''; ?>">
                    <?php echo h($item['label']); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <!-- Separator before logout -->
        <div class="sidebar__nav-separator"></div>
        
        <!-- Logout -->
        <a href="/DEV_matrac.uk/public/logout.php" class="sidebar__nav-item">
            Logout
        </a>
    </nav>
</aside>
