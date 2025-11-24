<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= h(generateCsrfToken()) ?>">
    <title><?= $pageTitle ?? 'MatraC' ?> - Material Traceability</title>


    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">

    <!-- CSS -->
    <link rel=" stylesheet" href="<?= asset('css/reset.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/forms.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/tables.css') ?>">

    <!-- Base URL for JavaScript -->
    <script>
        const BASE_URL = '<?= url('/') ?>';
    </script>
</head>

<body>
    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar__logo">MatraC</div>

        <nav class="topbar__breadcrumb" aria-label="Breadcrumb">
            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <span class="topbar__breadcrumb-separator">/</span>
                    <?php endif; ?>

                    <?php if (!empty($crumb['url'])): ?>
                        <a href="<?= h($crumb['url']) ?>" class="topbar__breadcrumb-item">
                            <?= h($crumb['label']) ?>
                        </a>
                    <?php else: ?>
                        <span class="topbar__breadcrumb-item topbar__breadcrumb-item--active">
                            <?= h($crumb['label']) ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Sidebar Toggle -->
    <button class="sidebar-tab" aria-label="Toggle menu">
        <span class="sidebar-tab__icon">☰</span>
    </button>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div class="content">
            <!-- Flash Messages -->
            <?php if ($successMessage = flash('success')): ?>
                <div class="alert alert--success">
                    <div class="alert__message"><?= h($successMessage) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage = flash('error')): ?>
                <div class="alert alert--error">
                    <div class="alert__message"><?= h($errorMessage) ?></div>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer__left">
            <span>MatraC v1.0.0-dev</span>
            <span>&copy; <?= date('Y') ?> Material Traceability System</span>
        </div>
        <div class="footer__right">
            <a href="<?= url('/help') ?>" class="footer__link">Help</a>
            <a href="mailto:support@matrac.uk" class="footer__link">Support</a>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= asset('js/utils.js') ?>"></script>
    <script src="<?= asset('js/sidebar.js') ?>"></script>

    <?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?= asset($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>