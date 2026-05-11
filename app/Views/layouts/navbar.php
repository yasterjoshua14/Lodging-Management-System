<?php
/**
 * @var string $brandCaption
 * @var string $brandName
 * @var string $brandMark
 * @var string|null $brandLogoUrl
 * @var string $contentTitle
 * @var string $currentUserEmail
 * @var string $currentUserName
 * @var string $currentUserRole
 */

$searchAction = portal_path($currentUserRole, 'search');
$searchQuery  = trim((string) service('request')->getGet('q'));
$searchPlaceholder = $currentUserRole === 'admin'
    ? 'Search rooms, bookings, tenants...'
    : 'Search bookings, dates, account...';
?>
<header class="shell-panel header-panel">
    <div class="header-panel__body">
        <div class="brand-block">
            <div class="brand-mark <?= $brandLogoUrl !== null ? 'brand-mark--logo' : '' ?>">
                <?php if ($brandLogoUrl !== null): ?>
                    <img src="<?= view_esc($brandLogoUrl, 'attr') ?>" alt="<?= view_esc($brandName . ' logo', 'attr') ?>">
                <?php else: ?>
                    <?= view_esc($brandMark) ?>
                <?php endif; ?>
            </div>

            <div class="brand-copy">
                <h1><?= view_esc($brandName) ?></h1>
                <p><?= view_esc($brandCaption) ?></p>
            </div>
        </div>

        <form action="<?= view_esc($searchAction) ?>" method="get" class="navbar-search" role="search">
            <label class="sr-only" for="navbar-search">Search the portal</label>
            <input
                type="search"
                id="navbar-search"
                name="q"
                class="navbar-search__input"
                value="<?= view_esc($searchQuery) ?>"
                placeholder="<?= view_esc($searchPlaceholder) ?>"
            >
            <button type="submit" class="navbar-search__button">Search</button>
        </form>

        <div class="header-actions">
            <button
                type="button"
                class="btn btn-ghost theme-toggle"
                data-theme-toggle
                aria-pressed="true"
                aria-label="Switch to light mode"
                title="Switch to light mode"
            >
                <span class="sr-only">Toggle color theme</span>
                <span class="theme-toggle__track" aria-hidden="true">
                    <span class="theme-toggle__icon theme-toggle__icon--sun">
                        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                            <path d="M12 3.75a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0V4.5a.75.75 0 0 1 .75-.75Zm0 14.25a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 12 18Zm8.25-6.75a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1 0-1.5h1.5ZM6.75 12a.75.75 0 0 1-.75.75H4.5a.75.75 0 0 1 0-1.5H6a.75.75 0 0 1 .75.75Zm9.084-5.334a.75.75 0 0 1 1.06 0l1.061 1.061a.75.75 0 0 1-1.06 1.06l-1.061-1.06a.75.75 0 0 1 0-1.061ZM7.106 15.894a.75.75 0 0 1 1.06 0l1.061 1.061a.75.75 0 1 1-1.06 1.06l-1.061-1.06a.75.75 0 0 1 0-1.061Zm10.849 0a.75.75 0 0 1 0 1.06l-1.06 1.061a.75.75 0 0 1-1.061-1.06l1.06-1.061a.75.75 0 0 1 1.061 0ZM8.166 6.666a.75.75 0 0 1 0 1.06L7.105 8.788a.75.75 0 0 1-1.06-1.06l1.06-1.061a.75.75 0 0 1 1.061 0ZM12 8.25A3.75 3.75 0 1 1 8.25 12 3.75 3.75 0 0 1 12 8.25Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="theme-toggle__icon theme-toggle__icon--moon">
                        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                            <path d="M14.63 3.183a.75.75 0 0 1 .867.98 7.5 7.5 0 1 0 9.34 9.34.75.75 0 0 1 .98.867 9 9 0 1 1-11.187-11.187Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="theme-toggle__thumb"></span>
                </span>
            </button>

            <div class="user-chip">
                <div class="avatar"><?= view_esc(strtoupper(substr($currentUserName, 0, 1))) ?></div>

                <div class="user-chip__copy">
                    <strong><?= view_esc($currentUserName) ?></strong>
                    <span><?= view_esc($currentUserEmail !== '' ? $currentUserEmail : ucfirst($currentUserRole)) ?></span>
                </div>
            </div>

            <form action="<?= view_esc(site_url('logout')) ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-ghost">Logout</button>
            </form>
        </div>
    </div>
</header>
