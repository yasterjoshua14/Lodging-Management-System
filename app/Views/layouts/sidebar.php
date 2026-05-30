<?php
/**
 * @var string $brandCaption
 * @var string $brandMark
 * @var string $currentUserEmail
 * @var string $currentUserName
 * @var string $currentUserRole
 * @var list<array{label:string, href:string, pattern:string, icon?:string}> $navItems
 */

$navIcons = [
    'account' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'bookings' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>',
    'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    'rooms' => '<path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/>',
    'tenants' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
];
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
                <?php
                    $isActive = url_is($item['pattern']);
                    $iconMarkup = $navIcons[$item['icon'] ?? ''] ?? $navIcons['dashboard'];
                ?>
                <a class="sidebar-link <?= $isActive ? 'is-active' : '' ?>" href="<?= view_esc($item['href']) ?>">
                    <span class="sidebar-link__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <?= $iconMarkup ?>
                        </svg>
                    </span>
                    <span class="sidebar-link__label"><?= view_esc($item['label']) ?></span>
                    <!-- <span class="sidebar-link__meta"><?= $isActive ? 'Current' : 'Open' ?></span> -->
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</aside>
