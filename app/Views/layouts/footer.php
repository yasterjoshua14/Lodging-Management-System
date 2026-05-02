<?php
/**
 * @var string $brandLabel
 * @var string $footerSummary
 */
?>
<footer class="shell-panel footer-panel">
    <div class="footer-panel__body">
        <p><?= view_esc($footerSummary) ?></p>
        <strong><?= view_esc($brandLabel) ?> • <?= date('Y') ?></strong>
    </div>
</footer>
