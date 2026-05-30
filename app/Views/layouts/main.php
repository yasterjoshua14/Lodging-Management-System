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
$brandName   = 'Hapit Anay Restobar and Lodge';
$brandCaption = $isAdminApp ? 'Protected management workspace for rooms, tenants, and bookings.'
                            : 'Tenant workspace for reservations, account details, and stay history.';
$brandMark = 'LMS';

$navItems = $isAdminApp
    ? [
        ['label' => 'Dashboard', 'href' => admin_path('dashboard'), 'pattern' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Rooms', 'href' => admin_path('rooms'), 'pattern' => 'rooms*', 'icon' => 'rooms'],
        ['label' => 'Tenants', 'href' => admin_path('tenants'), 'pattern' => 'tenants*', 'icon' => 'tenants'],
        ['label' => 'Bookings', 'href' => admin_path('bookings'), 'pattern' => 'bookings*', 'icon' => 'bookings'],
    ]
    : [
        ['label' => 'Dashboard', 'href' => tenant_path('dashboard'), 'pattern' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Rooms', 'href' => tenant_path('myRooms'), 'pattern' => 'myRooms*', 'icon' => 'rooms'],
        ['label' => 'My Bookings', 'href' => tenant_path('myBookings'), 'pattern' => 'myBookings', 'icon' => 'bookings'],
        ['label' => 'My Account', 'href' => tenant_path('myAccount'), 'pattern' => 'myAccount', 'icon' => 'account'],
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
$brandLogoPath = APPPATH . 'Views\theme\img\image.png';
$brandLogoUrl = null;
$faviconPath = FCPATH . 'favicon.ico';
$faviconUrl = base_url('favicon.ico');
$themeStorageKey = 'lms-theme';
$sidebarStorageKey = 'lms-sidebar';
$alertsScript = '';
$alertsScriptPath = APPPATH . 'Views/partials/alerts.js';
$usePopupAlerts = $currentUser !== null;

if (is_file($themeCssPath)) {
    $themeStylesheetUrl .= '?v=' . rawurlencode((string) filemtime($themeCssPath));
}

if (is_file($brandLogoPath)) {
    $brandLogoUrl = site_url('assets/theme/img/image.png') . '?v=' . rawurlencode((string) filemtime($brandLogoPath));
}

if (is_file($faviconPath)) {
    $faviconUrl .= '?v=' . rawurlencode((string) filemtime($faviconPath));
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
    <link rel="icon" type="image/x-icon" href="<?= view_esc($faviconUrl, 'attr') ?>">
    <link rel="shortcut icon" href="<?= view_esc($faviconUrl, 'attr') ?>">
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
            const sidebarStorageKey = <?= json_encode($sidebarStorageKey) ?>;
            const root = document.documentElement;
            const themeToggle = document.querySelector('[data-theme-toggle]');
            const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
            const sidebarPanel = document.querySelector('[data-sidebar-panel]');
            const pageBody = document.body;
            const autoHoverMedia = window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 961px)');

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

            const updateSidebarToggle = () => {
                if (!sidebarToggle) {
                    return;
                }

                const isExpanded = !pageBody.classList.contains('sidebar-collapsed')
                    || pageBody.classList.contains('sidebar-hover-open');
                const toggleLabel = isExpanded ? 'Hide sidebar navigation' : 'Show sidebar navigation';

                sidebarToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                sidebarToggle.setAttribute('aria-label', toggleLabel);
                sidebarToggle.setAttribute('title', toggleLabel);
            };

            const getSavedSidebarState = () => {
                try {
                    const savedState = window.localStorage.getItem(sidebarStorageKey);

                    if (savedState === 'collapsed' || savedState === 'expanded') {
                        return savedState;
                    }
                } catch (error) {
                    // Keep the default sidebar state when storage is unavailable.
                }

                return 'expanded';
            };

            const saveSidebarState = (isCollapsed) => {
                try {
                    window.localStorage.setItem(sidebarStorageKey, isCollapsed ? 'collapsed' : 'expanded');
                } catch (error) {
                    // Ignore storage failures after the UI updates.
                }
            };

            const setSidebarHoverState = (isOpen) => {
                const canHoverOpen = autoHoverMedia.matches && pageBody.classList.contains('sidebar-collapsed');

                pageBody.classList.toggle('sidebar-hover-open', canHoverOpen && isOpen);
                updateSidebarToggle();
            };

            const refreshSidebarHoverState = () => {
                if (!sidebarPanel) {
                    setSidebarHoverState(false);
                    return;
                }

                const isFocusedInside = sidebarPanel.contains(document.activeElement);
                const isPointerInside = sidebarPanel.matches(':hover');

                setSidebarHoverState(isFocusedInside || isPointerInside);
            };

            const setSidebarState = (isCollapsed, shouldPersist = false) => {
                const nextIsCollapsed = autoHoverMedia.matches && isCollapsed;

                pageBody.classList.toggle('sidebar-collapsed', nextIsCollapsed);
                refreshSidebarHoverState();
                updateSidebarToggle();

                if (shouldPersist) {
                    saveSidebarState(nextIsCollapsed);
                }
            };

            const syncSidebarMode = () => {
                if (!sidebarPanel) {
                    return;
                }

                setSidebarState(getSavedSidebarState() === 'collapsed');
            };

            applyTheme(getTheme());
            syncSidebarMode();

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
                    setSidebarState(!pageBody.classList.contains('sidebar-collapsed'), true);
                });
            }

            if (sidebarPanel) {
                sidebarPanel.addEventListener('mouseenter', () => {
                    setSidebarHoverState(true);
                });

                sidebarPanel.addEventListener('mouseleave', () => {
                    setSidebarHoverState(false);
                });

                sidebarPanel.addEventListener('focusin', () => {
                    setSidebarHoverState(true);
                });

                sidebarPanel.addEventListener('focusout', (event) => {
                    const nextTarget = event.relatedTarget;

                    if (nextTarget instanceof Node && sidebarPanel.contains(nextTarget)) {
                        return;
                    }

                    setSidebarHoverState(false);
                });

                if (typeof autoHoverMedia.addEventListener === 'function') {
                    autoHoverMedia.addEventListener('change', syncSidebarMode);
                } else if (typeof autoHoverMedia.addListener === 'function') {
                    autoHoverMedia.addListener(syncSidebarMode);
                }
            }
        })();
    </script>
    <?php if ($usePopupAlerts && $alertsScript !== ''): ?>
        <script><?= $alertsScript ?></script>
    <?php endif; ?>
</body>
</html>
