<?php
/**
 * @var string $brandCaption
 * @var string $brandLabel
 * @var string $brandMark
 * @var string $currentUserEmail
 * @var string $currentUserName
 * @var string $currentUserRole
 * @var list<array{label:string, href:string, pattern:string}> $navItems
 */
?>
<aside class="shell-panel sidebar-panel">
    <div class="sidebar-panel__body">
        <div class="sidebar-card">
            <div class="sidebar-card__mark"><?= view_esc($brandMark) ?></div>

            <div>
                <strong><?= view_esc($brandLabel) ?></strong>
                <p><?= view_esc($brandCaption) ?></p>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Primary">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = url_is($item['pattern']); ?>
                <a class="sidebar-link <?= $isActive ? 'is-active' : '' ?>" href="<?= view_esc($item['href']) ?>">
                    <span class="sidebar-link__label"><?= view_esc($item['label']) ?></span>
                    <span class="sidebar-link__meta"><?= $isActive ? 'Current' : 'Open' ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-foot">
            <p><?= view_esc(ucfirst($currentUserRole)) ?> access</p>
            <strong><?= view_esc($currentUserEmail !== '' ? $currentUserEmail : $currentUserName) ?></strong>
        </div>
    </div>
</aside>
