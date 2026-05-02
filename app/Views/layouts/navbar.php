<?php
/**
 * @var string $brandCaption
 * @var string $brandLabel
 * @var string $brandMark
 * @var string $contentTitle
 * @var string $currentUserEmail
 * @var string $currentUserName
 * @var string $currentUserRole
 */
?>
<header class="shell-panel header-panel">
    <div class="header-panel__body">
        <div class="brand-block">
            <div class="brand-mark"><?= view_esc($brandMark) ?></div>

            <div class="brand-copy">
                <span class="eyebrow"><?= view_esc($brandLabel) ?></span>
                <h1><?= view_esc($contentTitle) ?></h1>
                <p><?= view_esc($brandCaption) ?></p>
            </div>
        </div>

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
