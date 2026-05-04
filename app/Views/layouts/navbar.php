<?php
/**
 * @var string $brandCaption
 * @var string $brandName
 * @var string $brandMark
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
            <div class="brand-mark"><?= view_esc($brandMark) ?></div>

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
            <button type="submit" class="btn btn-secondary navbar-search__button">Search</button>
        </form>

        <div class="header-actions">
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
