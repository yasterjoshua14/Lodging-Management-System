<?php
/**
 * @var string $brandCaption
 * @var string $brandMark
 * @var string $currentUserEmail
 * @var string $currentUserName
 * @var string $currentUserRole
 * @var list<array{label:string, href:string, pattern:string}> $navItems
 */
?>
<aside class="shell-panel sidebar-panel" data-sidebar-panel>
    <div class="sidebar-panel__body">
        <button
            type="button"
            class="sidebar-toggle"
            data-sidebar-toggle
            aria-expanded="true"
            aria-label="Toggle navigation menu"
            title="Toggle navigation menu"
        >
            <span class="sr-only">Toggle navigation menu</span>
            <span class="sidebar-toggle__icon" aria-hidden="true">&#9776;</span>
        </button>

        <nav class="sidebar-nav" aria-label="Primary">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = url_is($item['pattern']); ?>
                <a class="sidebar-link <?= $isActive ? 'is-active' : '' ?>" href="<?= view_esc($item['href']) ?>" data-label-short="<?= view_esc(substr($item['label'], 0, 1)) ?>">
                    <span class="sidebar-link__label"><?= view_esc($item['label']) ?></span>
                    <!-- <span class="sidebar-link__meta"><?= $isActive ? 'Current' : 'Open' ?></span> -->
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</aside>
