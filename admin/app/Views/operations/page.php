<?php
use App\Core\Auth;
use App\Core\Csrf;

function nh_workspace_e(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$title = $title ?? 'Workspace';
$content = $content ?? '';
$base = rtrim((string) ($base ?? APP_BASE_PATH), '/');
$section = $section ?? 'operations';

$isManagement = $section === 'management';

$navItems = $isManagement
    ? [
        ['/admin/management', 'Overview'],
        ['/admin/management/reports', 'Reports'],
        ['/admin/management/staff', 'Staff & Access'],
        ['/admin/management/roles', 'Roles'],
        ['/admin/management/audit', 'Audit Trail'],
        ['/admin/management/settings', 'Settings'],
        ['/admin/management/content', 'Website Content'],
    ]
    : [
        ['/admin/operations', 'Overview'],
        ['/admin/destinations', 'Destinations'],
        ['/admin/suppliers', 'Suppliers'],
        ['/admin/quotations', 'Quotations'],
        ['/admin/bookings', 'Bookings'],
        ['/admin/payments', 'Payments'],
    ];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title><?= nh_workspace_e($title) ?> · Nature Holidays</title>
<style>


/* Premium workspace layout */
.app-shell {
    min-height: 100vh;
    display: flex;
}

.sidebar {
    width: 250px;
    flex: 0 0 250px;
    min-height: 100vh;
    padding: 28px 17px;
    background: var(--ink);
    color: #fff;
    display: flex;
    flex-direction: column;
}

.sidebar-brand {
    display: block;
    color: #fff;
    padding: 0 12px 26px;
    border-bottom: 1px solid rgba(255,255,255,.13);
}

.sidebar-brand strong {
    display: block;
    font-size: 17px;
    letter-spacing: .06em;
}

.sidebar-brand span {
    display: block;
    color: #bdcbbf;
    font-size: 11px;
    letter-spacing: .15em;
    margin-top: 5px;
}

.sidebar-label {
    color: #9eb3a3;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .16em;
    text-transform: uppercase;
    padding: 25px 12px 10px;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.sidebar-nav a {
    display: block;
    padding: 11px 12px;
    border-radius: 9px;
    color: #dce6dd;
    font-size: 13px;
    font-weight: 600;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: var(--green-soft);
    color: var(--green-dark);
    text-decoration: none;
}

.sidebar-bottom {
    margin-top: auto;
    padding: 18px 10px 0;
    border-top: 1px solid rgba(255,255,255,.13);
    color: #bdcbbf;
    font-size: 12px;
}

.sidebar-bottom strong {
    display: block;
    color: #fff;
    font-size: 13px;
}

.workspace {
    flex: 1;
    min-width: 0;
}

.topbar {
    min-height: 76px;
    padding: 14px 34px;
    background: #fff;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.topbar-title {
    color: var(--muted);
    font-size: 13px;
}

.topbar-title strong {
    display: block;
    color: var(--ink);
    font-size: 16px;
}

.user-chip {
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 13px;
    font-weight: 700;
}

.user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--green-soft);
    color: var(--green);
}

.workspace .wrap {
    width: min(1440px, 100%);
    margin: 0 auto;
    padding: 34px;
    min-height: calc(100vh - 76px);
}

@media (max-width: 900px) {
    .sidebar {
        width: 210px;
        flex-basis: 210px;
    }
    .workspace .wrap { padding: 26px 22px; }
    .topbar { padding: 14px 22px; }
}

@media (max-width: 700px) {
    .app-shell { display: block; }

    .sidebar {
        width: 100%;
        min-height: auto;
        padding: 14px;
    }

    .sidebar-brand { padding: 2px 7px 12px; }
    .sidebar-brand strong { font-size: 15px; }
    .sidebar-label { padding: 13px 7px 7px; }

    .sidebar-nav {
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 5px;
    }

    .sidebar-nav a {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .sidebar-bottom { display: none; }
    .topbar { min-height: 62px; padding: 12px 16px; }
    .topbar-title { display: none; }

    .workspace .wrap {
        width: 100%;
        padding: 22px 14px;
    }
}


:root {
    --ink: #20372b;
    --muted: #718076;
    --green: #28543b;
    --green-dark: #1d422d;
    --green-soft: #eaf1e9;
    --line: #e3e9e2;
    --paper: #f5f7f3;
    --white: #fff;
    --gold: #b58c4c;
    --red: #a33d32;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    background: var(--paper);
    color: var(--ink);
    font: 15px/1.5 Arial, sans-serif;
}

a {
    color: var(--green);
    text-decoration: none;
}

a:hover { text-decoration: underline; }

.topbar {
    background: #fff;
    border-bottom: 1px solid var(--line);
    padding: 15px max(20px, calc((100% - 1180px) / 2));
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.brand {
    color: var(--ink);
    font-weight: 800;
    letter-spacing: .01em;
}

.nav {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
}

.nav a {
    font-size: 14px;
    font-weight: 600;
}

.top-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.muted { color: var(--muted); }

.wrap {
    width: min(1180px, calc(100% - 36px));
    margin: 34px auto 70px;
}

.workspace-actions {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 18px;
}

.dashboard-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--green);
    font-size: 13px;
    font-weight: 700;
}

.dashboard-link:hover { text-decoration: underline; }

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--green);
    background: var(--green);
    color: #fff;
    border-radius: 8px;
    padding: 10px 15px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
}

.btn:hover {
    background: var(--green-dark);
    color: #fff;
    text-decoration: none;
}

.btn.secondary {
    background: #fff;
    color: var(--green);
}

.btn.small {
    padding: 7px 10px;
    font-size: 12px;
}

.btn:focus,
button:focus,
input:focus,
select:focus,
textarea:focus {
    outline: 3px solid #b9d4bf;
    outline-offset: 1px;
}

.flash,
.notice {
    background: #edf5ed;
    border: 1px solid #d6e7d7;
    padding: 12px 14px;
    border-radius: 9px;
    margin-bottom: 18px;
}

.flash.warning {
    background: #fff3d7;
    border-color: #f0dfb8;
}

.error {
    background: #fff0ed;
    border: 1px solid #f2d0c9;
    color: #963b30;
    padding: 12px 14px;
    border-radius: 9px;
    margin-bottom: 18px;
}

.footer {
    color: var(--muted);
    font-size: 12px;
    text-align: center;
    padding: 20px;
}

/* Shared content styling */
.wrap h1 {
    margin: 4px 0 12px;
    font-size: 30px;
    letter-spacing: -.03em;
}

.wrap h2 { font-size: 18px; }

.wrap .panel,
.wrap .card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 22px;
    margin: 18px 0;
    box-shadow: 0 4px 18px rgba(30,55,40,.035);
}

.wrap table {
    width: 100%;
    border-collapse: collapse;
    min-width: 680px;
}

.wrap th,
.wrap td {
    text-align: left;
    padding: 13px 12px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
}

.wrap th {
    font-size: 11px;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
}

.wrap td { font-size: 14px; }

.wrap label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin: 12px 0 6px;
}

.wrap input,
.wrap select,
.wrap textarea {
    width: 100%;
    border: 1px solid #d7e0d6;
    border-radius: 8px;
    padding: 11px 12px;
    background: #fff;
    color: var(--ink);
    font: inherit;
}

.wrap textarea {
    min-height: 100px;
    resize: vertical;
}

.wrap .bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    margin: 16px 0;
}

.wrap .inline { display: inline; }

.wrap button {
    border: 0;
    border-radius: 8px;
    padding: 9px 13px;
    background: var(--green);
    color: #fff;
    font: inherit;
    font-weight: 700;
    cursor: pointer;
}

.wrap button:hover { background: var(--green-dark); }

.wrap button.danger {
    background: var(--red);
}

.wrap .grid,
.wrap .cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 12px;
}

.wrap .num {
    display: block;
    color: var(--green);
    font-size: 26px;
    font-weight: 700;
}

.wrap .table-wrap,
.wrap .panel {
    overflow-x: auto;
}

@media (max-width: 700px) {
    .wrap {
        width: calc(100% - 24px);
        margin-top: 22px;
    }

    .topbar { padding: 14px 16px; }
    .nav { gap: 12px; }
    .top-actions {
        width: 100%;
        justify-content: space-between;
    }

    .wrap h1 { font-size: 25px; }

    .wrap .panel,
    .wrap .card { padding: 16px; }

    .wrap .grid,
    .wrap .cards { grid-template-columns: 1fr; }
}

</style>
</head>


<body>
<?php
$currentPath = rtrim(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
    '/'
);
$currentPath = $currentPath ?: '/';
$userName = Auth::user()['name'] ?? 'Admin';
$initial = strtoupper(substr($userName, 0, 1));
?>

<div class="app-shell">

    <aside class="sidebar">
        <a class="sidebar-brand" href="<?= nh_workspace_e($base) ?>/admin">
            <strong>NATURE HOLIDAYS</strong>
            <span>ADMIN WORKSPACE</span>
        </a>

        <div class="sidebar-label">
            <?= $isManagement ? 'Management' : 'Operations' ?>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($navItems as [$path, $label]): ?>
                <?php
                $active = $currentPath === $base . $path
                    || str_starts_with($currentPath, $base . $path . '/');
                ?>
                <a
                    href="<?= nh_workspace_e($base . $path) ?>"
                    class="<?= $active ? 'active' : '' ?>"
                    <?= $active ? 'aria-current="page"' : '' ?>
                >
                    <?= nh_workspace_e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <strong><?= nh_workspace_e($userName) ?></strong>
            Nature Holidays · Internal workspace
        </div>
    </aside>

    <div class="workspace">

        <header class="topbar">
            <div class="topbar-title">
                <strong><?= nh_workspace_e($title) ?></strong>
                <?= $isManagement ? 'Manage your business' : 'Manage your travel operations' ?>
            </div>

            <div class="top-actions">
                <div class="user-chip">
                    <span class="user-avatar"><?= nh_workspace_e($initial) ?></span>
                    <?= nh_workspace_e($userName) ?>
                </div>

                <form method="post" action="<?= nh_workspace_e($base) ?>/logout">
                    <input type="hidden" name="_csrf" value="<?= nh_workspace_e(Csrf::token()) ?>">
                    <button class="btn secondary small" type="submit">Sign out</button>
                </form>
            </div>
        </header>

        <main class="wrap">
            <?= $content ?>


        </main>

        <footer class="footer">
            Nature Holidays · Internal business workspace
        </footer>

    </div>
</div>
</body>
</html>