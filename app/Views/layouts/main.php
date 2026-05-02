<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var array<string, mixed>|null $currentUser
 * @var string|null $authSurface
 * @var string|null $title
 */

$currentUser  = $currentUser ?? null;
$authSurface  = $authSurface ?? (service('uri')->getSegment(1) === 'admin' ? 'admin' : 'tenant');
$activeRole   = $currentUser['role'] ?? $authSurface;
$isAdminApp   = $activeRole === 'admin';
$currentUserName  = (string) ($currentUser['name'] ?? 'User');
$currentUserEmail = (string) ($currentUser['email'] ?? '');
$currentUserRole  = (string) ($currentUser['role'] ?? $authSurface);
$brandLabel   = $isAdminApp ? 'Admin Operations' : 'Tenant Portal';
$brandCaption = $isAdminApp
    ? 'Protected management workspace for rooms, tenants, and bookings.'
    : 'Tenant workspace for reservations, account details, and stay history.';
$brandMark = $isAdminApp ? 'AD' : 'TP';

$palette = $isAdminApp
    ? [
        'accent'      => '#d4a066',
        'accentDark'  => '#8b5e34',
        'accentSoft'  => 'rgba(212, 160, 102, 0.18)',
        'accentGhost' => 'rgba(212, 160, 102, 0.10)',
        'background'  => 'radial-gradient(circle at top left, rgba(212, 160, 102, 0.14), transparent 28%), linear-gradient(180deg, #15181c 0%, #1a2024 48%, #111417 100%)',
    ]
    : [
        'accent'      => '#78c8d5',
        'accentDark'  => '#0f6d84',
        'accentSoft'  => 'rgba(120, 200, 213, 0.18)',
        'accentGhost' => 'rgba(120, 200, 213, 0.10)',
        'background'  => 'radial-gradient(circle at top left, rgba(120, 200, 213, 0.14), transparent 28%), linear-gradient(180deg, #14191d 0%, #172126 48%, #101518 100%)',
    ];

$navItems = $isAdminApp
    ? [
        ['label' => 'Dashboard', 'href' => admin_path('admin-dashboard'), 'pattern' => 'admin-dashboard'],
        ['label' => 'Rooms', 'href' => admin_path('rooms'), 'pattern' => 'rooms*'],
        ['label' => 'Tenants', 'href' => admin_path('tenants'), 'pattern' => 'tenants*'],
        ['label' => 'Bookings', 'href' => admin_path('bookings'), 'pattern' => 'bookings*'],
    ]
    : [
        ['label' => 'Dashboard', 'href' => tenant_path('dashboard'), 'pattern' => 'dashboard'],
        ['label' => 'My Bookings', 'href' => tenant_path('myBookings'), 'pattern' => 'myBookings'],
        ['label' => 'My Account', 'href' => tenant_path('myAccount'), 'pattern' => 'myAccount'],
    ];

$activeNavItem = null;

foreach ($navItems as $item) {
    if (url_is($item['pattern'])) {
        $activeNavItem = $item;
        break;
    }
}

$contentTitle = trim((string) ($title ?? ''));
$contentTitle = $contentTitle !== '' ? $contentTitle : ($activeNavItem['label'] ?? 'Content');

$footerSummary = $isAdminApp
    ? 'Monitor inventory, bookings, and tenant records from one control surface.'
    : 'Review reservations, account details, and upcoming stays from one portal.';

$themeCss = '';
$themeCssPath = APPPATH . 'Views/theme/style.css';
$alertsScript = '';
$alertsScriptPath = APPPATH . 'Views/partials/alerts.js';
$usePopupAlerts = $currentUser !== null;

if (is_file($themeCssPath)) {
    $loadedThemeCss = file_get_contents($themeCssPath);

    if (is_string($loadedThemeCss)) {
        $themeCss = $loadedThemeCss;
    }
}

if (is_file($alertsScriptPath)) {
    $loadedAlertsScript = file_get_contents($alertsScriptPath);

    if (is_string($loadedAlertsScript)) {
        $alertsScript = $loadedAlertsScript;
    }
}

$alertsMarkup  = view('partials/alerts', ['usePopupAlerts' => $usePopupAlerts]);
$contentMarkup = $this->renderSection('content');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= view_esc($title ?? 'Lodging Management System') ?></title>
    <style>
        :root {
            --surface-base: #161b1f;
            --surface-panel: #252c31;
            --surface-panel-strong: #2c353b;
            --surface-panel-soft: #1d2328;
            --surface-elevated: #30393f;
            --ink: #f3f7f9;
            --muted: #a7b2b8;
            --line: rgba(255, 255, 255, 0.07);
            --line-strong: rgba(255, 255, 255, 0.13);
            --outline: #080b0d;
            --tag-bg: #090b0d;
            --success: #73d6a7;
            --success-soft: rgba(115, 214, 167, 0.14);
            --warning: #f1bf68;
            --warning-soft: rgba(241, 191, 104, 0.14);
            --danger: #f29a9a;
            --danger-soft: rgba(242, 154, 154, 0.14);
            --info-soft: rgba(120, 200, 213, 0.14);
            --shadow: 0 20px 48px rgba(0, 0, 0, 0.34);
            --radius-xl: 22px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --accent: <?= view_esc($palette['accent']) ?>;
            --accent-dark: <?= view_esc($palette['accentDark']) ?>;
            --accent-soft: <?= view_esc($palette['accentSoft']) ?>;
            --accent-ghost: <?= view_esc($palette['accentGhost']) ?>;
            --background-wash: <?= $palette['background'] ?>;
        }
    </style>
    <?php if ($themeCss !== ''): ?>
        <style><?= $themeCss ?></style>
    <?php endif; ?>
</head>
<body class="app-body <?= $currentUser === null ? 'app-body--auth' : 'app-body--dashboard' ?>">
    <div class="page-shell">
        <?php if ($currentUser !== null): ?>
            <?= view('layouts/navbar', [
                'brandCaption'     => $brandCaption,
                'brandLabel'       => $brandLabel,
                'brandMark'        => $brandMark,
                'contentTitle'     => $contentTitle,
                'currentUserEmail' => $currentUserEmail,
                'currentUserName'  => $currentUserName,
                'currentUserRole'  => $currentUserRole,
            ]) ?>

            <div class="dashboard-grid">
                <?= view('layouts/sidebar', [
                    'brandCaption'     => $brandCaption,
                    'brandLabel'       => $brandLabel,
                    'brandMark'        => $brandMark,
                    'currentUserEmail' => $currentUserEmail,
                    'currentUserName'  => $currentUserName,
                    'currentUserRole'  => $currentUserRole,
                    'navItems'         => $navItems,
                ]) ?>

                <?= view('layouts/content', [
                    'alertsMarkup'  => $alertsMarkup,
                    'contentMarkup' => $contentMarkup,
                ]) ?>
            </div>

            <?= view('layouts/footer', [
                'brandLabel'    => $brandLabel,
                'footerSummary' => $footerSummary,
            ]) ?>
        <?php else: ?>
            <main class="auth-wrap">
                <section class="shell-panel auth-panel">
                    <div class="auth-panel__body">
                        <?= $alertsMarkup ?>
                        <?= $contentMarkup ?>
                    </div>
                </section>
            </main>
        <?php endif; ?>
    </div>
    <?php if ($usePopupAlerts && $alertsScript !== ''): ?>
        <script><?= $alertsScript ?></script>
    <?php endif; ?>
</body>
</html>
