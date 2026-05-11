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
$brandName   = 'businessName';
$brandCaption = $isAdminApp ? 'Protected management workspace for rooms, tenants, and bookings.'
                            : 'Tenant workspace for reservations, account details, and stay history.';
$brandMark = 'LMS';

$navItems = $isAdminApp
    ? [
        ['label' => 'Dashboard', 'href' => admin_path('dashboard'), 'pattern' => 'dashboard'],
        ['label' => 'Rooms', 'href' => admin_path('rooms'), 'pattern' => 'rooms*'],
        ['label' => 'Tenants', 'href' => admin_path('tenants'), 'pattern' => 'tenants*'],
        ['label' => 'Bookings', 'href' => admin_path('bookings'), 'pattern' => 'bookings*'],
    ]
    : [
        ['label' => 'Dashboard', 'href' => tenant_path('dashboard'), 'pattern' => 'dashboard'],
        ['label' => 'Rooms', 'href' => tenant_path('myRooms'), 'pattern' => 'myRooms*'],
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

$themeCssPath = APPPATH . 'Views/theme/style.css';
$themeStylesheetUrl = site_url('assets/theme');
$brandLogoPath = APPPATH . 'Views/theme/img/image1logo.png';
$brandLogoUrl = null;
$themeStorageKey = 'lms-theme';
$alertsScript = '';
$alertsScriptPath = APPPATH . 'Views/partials/alerts.js';
$usePopupAlerts = $currentUser !== null;

if (is_file($themeCssPath)) {
    $themeStylesheetUrl .= '?v=' . rawurlencode((string) filemtime($themeCssPath));
}

if (is_file($brandLogoPath)) {
    $brandLogoUrl = site_url('assets/theme/img/image1logo.png') . '?v=' . rawurlencode((string) filemtime($brandLogoPath));
}

if (is_file($alertsScriptPath)) {
    $loadedAlertsScript = file_get_contents($alertsScriptPath);

    if (is_string($loadedAlertsScript)) {
        $alertsScript = $loadedAlertsScript;
    }
}

$alertsMarkup  = view('partials/alerts', ['usePopupAlerts' => $usePopupAlerts]);
$contentMarkup = $this->renderSection('content');
$bodyClasses = implode(' ', [
    'app-body',
    $currentUser === null ? 'app-body--auth' : 'app-body--dashboard',
    $isAdminApp ? 'app-body--admin' : 'app-body--tenant',
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= view_esc($title ?? 'Lodging Management System') ?></title>
    <script>
        (() => {
            const storageKey = <?= json_encode($themeStorageKey) ?>;
            const root = document.documentElement;

            try {
                const savedTheme = window.localStorage.getItem(storageKey);

                if (savedTheme === 'light' || savedTheme === 'dark') {
                    root.dataset.theme = savedTheme;
                    return;
                }
            } catch (error) {
                // Keep the default theme when storage is unavailable.
            }

            root.dataset.theme = 'dark';
        })();
    </script>
    <link rel="stylesheet" href="<?= view_esc($themeStylesheetUrl, 'attr') ?>">
</head>
<body class="<?= view_esc($bodyClasses, 'attr') ?>">
    <div class="page-shell">
        <?php if ($currentUser !== null): ?>
            <?= view('layouts/navbar', [
                'brandCaption'     => $brandCaption,
                'brandMark'        => $brandMark,
                'brandLogoUrl'     => $brandLogoUrl,
                'brandName'       => $brandName,
                'contentTitle'     => $contentTitle,
                'currentUserEmail' => $currentUserEmail,
                'currentUserName'  => $currentUserName,
                'currentUserRole'  => $currentUserRole,
            ]) ?>

            <div class="dashboard-grid">
                <?= view('layouts/sidebar', [
                    'brandCaption'     => $brandCaption,
                    'brandMark'        => $brandMark,
                    'brandName'       => $brandName,
                    'currentUserEmail' => $currentUserEmail,
                    'currentUserName'  => $currentUserName,
                    'currentUserRole'  => $currentUserRole,
                    'navItems'         => $navItems,
                ]) ?>

                <?= view('layouts/content', [
                    'brandName'       => $brandName,
                    'alertsMarkup'  => $alertsMarkup,
                    'contentMarkup' => $contentMarkup,
                ]) ?>
            </div>

            <?= view('layouts/footer', [
            ]) ?>
        <?php else: ?>
            <main class="auth-wrap">
                <section class="shell-panel auth-panel">
                    <div class="auth-panel__body">
                        <div class="auth-brand">
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

                        <?= $alertsMarkup ?>
                        <?= $contentMarkup ?>
                    </div>
                </section>
            </main>
        <?php endif; ?>
    </div>
    <script>
        (() => {
            const storageKey = <?= json_encode($themeStorageKey) ?>;
            const root = document.documentElement;
            const themeToggle = document.querySelector('[data-theme-toggle]');
            const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
            const pageBody = document.body;

            const getTheme = () => root.dataset.theme === 'light' ? 'light' : 'dark';

            const applyTheme = (theme) => {
                const isDarkMode = theme === 'dark';
                const nextThemeLabel = isDarkMode ? 'Switch to light mode' : 'Switch to dark mode';

                root.dataset.theme = theme;

                if (!themeToggle) {
                    return;
                }

                themeToggle.setAttribute('aria-pressed', isDarkMode ? 'true' : 'false');
                themeToggle.setAttribute('aria-label', nextThemeLabel);
                themeToggle.setAttribute('title', nextThemeLabel);
            };

            const setSidebarState = (isCollapsed) => {
                pageBody.classList.toggle('sidebar-collapsed', isCollapsed);

                if (!sidebarToggle) {
                    return;
                }

                sidebarToggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            };

            applyTheme(getTheme());
            setSidebarState(false);

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const nextTheme = getTheme() === 'dark' ? 'light' : 'dark';

                    applyTheme(nextTheme);

                    try {
                        window.localStorage.setItem(storageKey, nextTheme);
                    } catch (error) {
                        // Ignore storage failures after the UI updates.
                    }
                });
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    const isCollapsed = pageBody.classList.toggle('sidebar-collapsed');
                    sidebarToggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                });
            }
        })();
    </script>
    <?php if ($usePopupAlerts && $alertsScript !== ''): ?>
        <script><?= $alertsScript ?></script>
    <?php endif; ?>
</body>
</html>
