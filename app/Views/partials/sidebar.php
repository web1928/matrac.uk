<?php
// Get current user
$currentUser = getCurrentUser();
$userRole = $currentUser['role'] ?? 'guest';
$firstName = $currentUser['first_name'] ?? 'Guest';
$lastName = $currentUser['last_name'] ?? 'User';
$fullName = trim($firstName . ' ' . $lastName);

// Get current page
$currentUri = $_SERVER['REQUEST_URI'];
$currentPath = parse_url($currentUri, PHP_URL_PATH);

// Define menu items
$menuItems = [
    ['label' => 'Dashboard', 'url' => '/dashboard', 'roles' => ['all']],
    ['label' => 'Goods Receipt', 'url' => '/goods-receipt', 'roles' => ['admin', 'goods_receptor', 'manager']],
    ['label' => 'Deboxing/Tempering', 'url' => '/deboxing', 'roles' => ['admin', 'goods_issuer', 'manager']],
    ['label' => 'Goods Issue', 'url' => '/goods-issue', 'roles' => ['admin', 'goods_issuer', 'manager']],
    ['label' => 'Mixing', 'url' => '/mixing', 'roles' => ['admin', 'mixer', 'manager']],
    ['label' => 'Active Mixes', 'url' => '/active-mixes', 'roles' => ['admin', 'mixer', 'manager']],
    ['label' => 'Inventory', 'url' => '/inventory', 'roles' => ['all']],
    ['label' => 'Rejected Stock', 'url' => '/rejected-stock', 'roles' => ['admin', 'qa', 'stock_manager', 'manager']],
    ['label' => 'Reports', 'url' => '/reports', 'roles' => ['admin', 'manager', 'qa']],
    ['separator' => true],
    ['label' => 'Settings', 'url' => '/settings', 'roles' => ['all']],
    ['label' => 'Help', 'url' => '/help', 'roles' => ['all']],
];

function canSeeMenuItem($item, $userRole)
{
    if (isset($item['separator'])) return true;
    if (in_array('all', $item['roles'])) return true;
    return in_array($userRole, $item['roles']);
}

function isActiveMenuItem($itemUrl, $currentPath)
{
    return rtrim($itemUrl, '/') === rtrim($currentPath, '/');
}
?>

<!-- Sidebar Backdrop -->
<div class="sidebar-backdrop"></div>

<!-- Sidebar Navigation -->
<aside class="sidebar">
    <!-- User Info Header -->
    <div class="sidebar__header">
        <div class="sidebar__user-name"><?= h($fullName) ?></div>
        <div class="sidebar__user-role"><?= h(ucwords(str_replace('_', ' ', $userRole))) ?></div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar__nav">
        <?php foreach ($menuItems as $item): ?>
            <?php if (!canSeeMenuItem($item, $userRole)) continue; ?>

            <?php if (isset($item['separator'])): ?>
                <div class="sidebar__nav-separator"></div>
            <?php else: ?>
                <a href="<?= url($item['url']) ?>"
                    class="sidebar__nav-item <?= isActiveMenuItem($item['url'], $currentPath) ? 'sidebar__nav-item--active' : '' ?>">
                    <?= h($item['label']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Separator before logout -->
        <div class="sidebar__nav-separator"></div>

        <!-- Logout -->
        <a href="<?= url('/logout') ?>" class="sidebar__nav-item">Logout</a>
    </nav>
</aside>