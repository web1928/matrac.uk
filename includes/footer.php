<?php
/**
 * Footer Include
 * Application footer with version and support links
 */

$appVersion = '1.0.0-dev';
$currentYear = date('Y');
?>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer__left">
            <span>MatraC v<?php echo h($appVersion); ?></span>
            <span>&copy; <?php echo $currentYear; ?> Material Traceability System</span>
        </div>
        <div class="footer__right">
            <a href="/DEV_matrac.uk/public/pages/help.php" class="footer__link">Help</a>
            <a href="mailto:support@matrac.uk" class="footer__link">Support</a>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="/DEV_matrac.uk/assets/js/utils.js"></script>
    <script src="/DEV_matrac.uk/assets/js/sidebar.js"></script>
    
    <?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo h($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
