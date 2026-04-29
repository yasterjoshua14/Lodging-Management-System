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
$currentUserRole  = (string) ($currentUser['role'] ?? 'user');
$brandLabel   = $isAdminApp ? 'Admin Operations' : 'Tenant Portal';
$brandCaption = $isAdminApp
    ? 'Protected management console for rooms, tenants, and bookings.'
    : 'Tenant portal for stays, booking history, and account details.';

$palette = $isAdminApp
    ? [
        'bg'           => '#f2ede5',
        'panel'        => '#fffaf4',
        'panelStrong'  => '#ffffff',
        'ink'          => '#263238',
        'muted'        => '#6f6a61',
        'line'         => '#ddcbb9',
        'accent'       => '#8b5e34',
        'accentDark'   => '#5e3b1d',
        'accentSoft'   => '#efdfcd',
        'success'      => '#2d7a5c',
        'successSoft'  => '#d7eee4',
        'warning'      => '#ae7320',
        'warningSoft'  => '#faebd4',
        'danger'       => '#b14b45',
        'dangerSoft'   => '#f6d8d5',
        'infoSoft'     => '#e8e2d9',
        'background'   => 'radial-gradient(circle at top left, rgba(255,255,255,0.88), transparent 34%), linear-gradient(145deg, #efe3d4 0%, #f7f1e8 44%, #e7eceb 100%)',
        'authGradient' => 'linear-gradient(155deg, rgba(94, 59, 29, 0.95), rgba(46, 30, 15, 0.98))',
        'markGradient' => 'linear-gradient(135deg, #8b5e34, #c79763)',
    ]
    : [
        'bg'           => '#edf5f7',
        'panel'        => '#f8fdff',
        'panelStrong'  => '#ffffff',
        'ink'          => '#143340',
        'muted'        => '#67818d',
        'line'         => '#c8dce3',
        'accent'       => '#0f6d84',
        'accentDark'   => '#0a465b',
        'accentSoft'   => '#d8edf3',
        'success'      => '#237357',
        'successSoft'  => '#d6efe4',
        'warning'      => '#9f6d1d',
        'warningSoft'  => '#f9ecd8',
        'danger'       => '#ba4d48',
        'dangerSoft'   => '#f6dbd8',
        'infoSoft'     => '#dbeaf0',
        'background'   => 'radial-gradient(circle at top left, rgba(255,255,255,0.9), transparent 34%), linear-gradient(145deg, #e2f0f4 0%, #f4fafb 44%, #d9ebe3 100%)',
        'authGradient' => 'linear-gradient(155deg, rgba(15, 109, 132, 0.94), rgba(10, 70, 91, 0.98))',
        'markGradient' => 'linear-gradient(135deg, #0f6d84, #6ca4b4)',
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= view_esc($title ?? 'Lodging Management System') ?></title>
    <style>
        :root {
            --bg: <?= view_esc($palette['bg']) ?>;
            --panel: <?= view_esc($palette['panel']) ?>;
            --panel-strong: <?= view_esc($palette['panelStrong']) ?>;
            --ink: <?= view_esc($palette['ink']) ?>;
            --muted: <?= view_esc($palette['muted']) ?>;
            --line: <?= view_esc($palette['line']) ?>;
            --accent: <?= view_esc($palette['accent']) ?>;
            --accent-dark: <?= view_esc($palette['accentDark']) ?>;
            --accent-soft: <?= view_esc($palette['accentSoft']) ?>;
            --warning: <?= view_esc($palette['warning']) ?>;
            --warning-soft: <?= view_esc($palette['warningSoft']) ?>;
            --danger: <?= view_esc($palette['danger']) ?>;
            --danger-soft: <?= view_esc($palette['dangerSoft']) ?>;
            --success: <?= view_esc($palette['success']) ?>;
            --success-soft: <?= view_esc($palette['successSoft']) ?>;
            --info-soft: <?= view_esc($palette['infoSoft']) ?>;
            --shadow: 0 20px 50px rgba(35, 45, 52, 0.12);
            --radius: 24px;
            --background-wash: <?= $palette['background'] ?>;
            --auth-gradient: <?= $palette['authGradient'] ?>;
            --mark-gradient: <?= $palette['markGradient'] ?>;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Trebuchet MS", "Gill Sans", "Segoe UI", sans-serif;
            color: var(--ink);
            background: var(--background-wash);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 18px 24px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.62);
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--mark-gradient);
            font-weight: 700;
            box-shadow: 0 14px 30px rgba(28, 48, 60, 0.18);
        }

        .brand h1,
        .brand p,
        .mini-item strong,
        .mini-item p,
        .detail-item h3,
        .detail-item p {
            margin: 0;
        }

        .brand h1 {
            font-size: 1rem;
            letter-spacing: 0.04em;
        }

        .brand p,
        .text-muted,
        .auth-hero p,
        .section-head p,
        .list-head p,
        .detail-item p {
            color: var(--muted);
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            padding: 10px 16px;
            border-radius: 999px;
            color: var(--muted);
            transition: 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--accent-soft);
            color: var(--accent-dark);
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--panel);
            border: 1px solid var(--line);
            color: var(--muted);
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--accent);
            font-weight: 700;
        }

        .role-tag {
            display: inline-flex;
            margin-top: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .main-card,
        .card {
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .main-card {
            padding: 28px;
        }

        .auth-wrap {
            min-height: calc(100vh - 64px);
            display: grid;
            place-items: center;
        }

        .auth-grid {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            gap: 24px;

            display: flex;
            justify-content: center;   /* horizontal center */
            align-items: center;       /* vertical center */
            min-height: 100vh;
            margin: 0;

            width: 500px;       /* slightly wider looks more natural */
            max-width: 90%;
            padding: 30px;
            border-radius: 16px;
            
        }

        .auth-hero {
            padding: 36px;
            color: #f8fcfb;
            background:
                linear-gradient(120deg, rgba(255, 255, 255, 0.08), transparent 40%),
                var(--auth-gradient);
        }

        .auth-kicker {
            display: inline-flex;
            margin-bottom: 18px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.84rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .auth-hero h2,
        .auth-panel h2,
        .section-head h2,
        .empty-state h3,
        .detail-item h3 {
            margin-top: 0;
        }

        .auth-hero h2 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            line-height: 1;
            margin-bottom: 14px;
        }

        .auth-hero p {
            max-width: 38ch;
            color: rgba(248, 252, 251, 0.78);
        }

        .feature-list {
            display: grid;
            gap: 14px;
            margin-top: 28px;
        }

        .feature-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .feature-item strong {
            display: block;
            margin-bottom: 6px;
        }

        .auth-panel {
            padding: 34px;
        }

        .section-head,
        .list-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            margin-bottom: 22px;
        }

        .card {
            padding: 22px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.92rem;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 700;
            margin: 0;
        }

        .stat-note {
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .form-grid,
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-grid .full-span,
        .detail-grid .full-span {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--panel-strong);
            color: var(--ink);
            font: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(15, 109, 132, 0.14);
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border: 0;
            border-radius: 999px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            box-shadow: 0 12px 24px rgba(15, 109, 132, 0.22);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .btn-secondary {
            color: var(--accent-dark);
            background: var(--accent-soft);
        }

        .btn-danger {
            color: var(--danger);
            background: var(--danger-soft);
        }

        .btn-ghost {
            color: var(--muted);
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid var(--line);
        }

        .button-row,
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .button-row {
            margin-top: 24px;
        }

        .alert-stack {
            display: grid;
            gap: 12px;
            margin-bottom: 18px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid;
        }

        .alert-success {
            background: var(--success-soft);
            color: var(--success);
            border-color: rgba(47, 125, 96, 0.24);
        }

        .alert-error {
            background: var(--danger-soft);
            color: var(--danger);
            border-color: rgba(177, 75, 69, 0.22);
        }

        .alert-warning {
            background: var(--warning-soft);
            color: var(--warning);
            border-color: rgba(194, 122, 39, 0.22);
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid rgba(200, 220, 227, 0.9);
            background: rgba(255, 255, 255, 0.56);
        }

        .sort-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid rgba(200, 220, 227, 0.76);
            background: rgba(255, 255, 255, 0.46);
        }

        .sort-bar strong,
        .sort-bar p {
            margin: 0;
        }

        .sort-bar p {
            margin-top: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th,
        td {
            padding: 16px 18px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid rgba(200, 220, 227, 0.8);
        }

        th {
            color: var(--muted);
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(255, 255, 255, 0.34);
        }

        .sort-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: inherit;
            font-weight: 700;
        }

        .sort-link:hover,
        .sort-link.active {
            color: var(--accent-dark);
        }

        .sort-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            padding: 4px 8px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-size: 0.72rem;
            line-height: 1;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .badge-success {
            color: var(--success);
            background: var(--success-soft);
        }

        .badge-warning {
            color: var(--warning);
            background: var(--warning-soft);
        }

        .badge-muted {
            color: #6b7280;
            background: #e5e7eb;
        }

        .badge-info {
            color: var(--accent-dark);
            background: var(--info-soft);
        }

        .document-cell {
            display: grid;
            gap: 10px;
        }

        .document-thumb {
            display: block;
            width: 92px;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.72);
        }

        .document-thumb img,
        .document-preview-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .document-preview {
            grid-column: 1 / -1;
            display: grid;
            gap: 14px;
        }

        .document-preview-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .document-preview-head label,
        .document-preview-head p {
            margin: 0;
        }

        .document-preview-head p {
            margin-top: 6px;
        }

        .document-preview-link {
            display: block;
            width: min(420px, 100%);
            max-width: 100%;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.72);
        }

        .document-preview-image {
            height: auto;
            max-height: 320px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.72);
        }

        .document-file-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--info-soft);
            color: var(--accent-dark);
            font-weight: 700;
            width: fit-content;
            max-width: 100%;
        }

        .empty-state {
            padding: 38px 24px;
            text-align: center;
            border: 1px dashed var(--line);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.38);
        }

        .split-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            gap: 22px;
        }

        .mini-list {
            display: grid;
            gap: 14px;
        }

        .mini-item,
        .detail-item {
            padding: 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid rgba(200, 220, 227, 0.76);
        }

        .mini-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .mini-item.compact {
            align-items: flex-start;
        }

        .mini-item.compact span {
            text-align: right;
        }

        .detail-item h3 {
            margin-bottom: 8px;
            font-size: 0.92rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .detail-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
        }

        .bar-list {
            display: grid;
            gap: 14px;
        }

        .bar-item {
            padding: 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid rgba(200, 220, 227, 0.76);
        }

        .bar-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 10px;
        }

        .bar-head strong,
        .bar-head p,
        .bar-meta p {
            margin: 0;
        }

        .bar-value {
            font-weight: 700;
            white-space: nowrap;
        }

        .bar-track {
            width: 100%;
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--accent-soft);
        }

        .bar-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }

        .bar-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.84rem;
        }

        .link-inline {
            color: var(--accent);
            font-weight: 700;
        }

        .auth-switch {
            margin-top: 16px;
            color: var(--muted);
        }

        .inline-form {
            display: inline;
        }

        @media (max-width: 920px) {
            .auth-grid,
            .split-grid,
            .form-grid,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                border-radius: 28px;
                flex-direction: column;
                align-items: stretch;
            }

            .nav {
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .page-shell {
                width: min(100% - 20px, 100%);
                padding-top: 10px;
            }

            .main-card,
            .card,
            .auth-panel,
            .auth-hero {
                padding: 22px;
            }

            .button-row,
            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .sort-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .document-preview-head {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <?php if ($currentUser !== null): ?>
            <header class="topbar">
                <div class="brand">
                    <div class="brand-mark"><?= $isAdminApp ? 'AD' : 'TP' ?></div>
                    <div>
                        <h1><?= view_esc($brandLabel) ?></h1>
                        <p><?= view_esc($brandCaption) ?></p>
                    </div>
                </div>

                <nav class="nav">
                    <?php foreach ($navItems as $item): ?>
                        <a class="nav-link <?= url_is($item['pattern']) ? 'active' : '' ?>" href="<?= view_esc($item['href']) ?>"><?= view_esc($item['label']) ?></a>
                    <?php endforeach; ?>
                </nav>

                <div class="actions">
                    <div class="user-chip">
                        <div class="avatar"><?= view_esc(strtoupper(substr($currentUserName, 0, 1))) ?></div>
                        <div>
                            <strong><?= view_esc($currentUserName) ?></strong><br>
                            <span><?= view_esc($currentUserEmail) ?></span><br>
                            <span class="role-tag"><?= view_esc($currentUserRole) ?></span>
                        </div>
                    </div>

                    <form action="<?= view_esc(site_url('logout')) ?>" method="post" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost">Logout</button>
                    </form>
                </div>
            </header>

            <main class="main-card">
                <?= $this->include('partials/alerts') ?>
                <?= $this->renderSection('content') ?>
            </main>
        <?php else: ?>
            <main class="auth-wrap">
                <div class="auth-grid">
                    <section class="auth-panel main-card">
                        <?= $this->include('partials/alerts') ?>
                        <?= $this->renderSection('content') ?>
                    </section>
                </div>
            </main>
        <?php endif; ?>
    </div>
</body>
</html>

