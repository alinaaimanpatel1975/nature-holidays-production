
<?php
use App\Core\Auth;
use App\Core\Csrf;

function nh_e(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$base = rtrim((string) APP_BASE_PATH, '/');
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentPath = rtrim($currentPath, '/') ?: '/';

$user = Auth::user() ?? [];
$userName = $user['name'] ?? 'Admin';
$initials = strtoupper(substr($userName, 0, 1));

$navItems = [
    ['Dashboard', '/admin', null],
    ['Customers', '/admin/customers', 'customers.view'],
    ['Enquiries & CRM', '/admin/enquiries', 'enquiries.view'],
    ['Packages', '/admin/packages', 'packages.manage'],
    ['Operations', '/admin/operations', 'operations.view'],
    ['Payments', '/admin/payments', 'payments.view'],
    ['Management Centre', '/admin/management', 'reports.view'],
];

function nh_nav_active(string $path, string $current): bool {
    if ($path === '/admin') {
        return $current === '/admin';
    }
    return $current === $path || str_starts_with($current, $path . '/');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title><?= nh_e($pageTitle ?? 'Workspace') ?> · Nature Holidays</title>

<style>
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
    --sidebar: #20372b;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    background: var(--paper);
    color: var(--ink);
    font: 15px/1.5 Arial, sans-serif;
}

a { color: var(--green); text-decoration: none; }
a:hover { text-decoration: underline; }

.app-shell {
    min-height: 100vh;
    display: flex;
}

.sidebar {
    width: 250px;
    flex: 0 0 250px;
    min-height: 100vh;
    background: var(--sidebar);
    color: #fff;
    padding: 28px 17px;
    display: flex;
    flex-direction: column;
}

.sidebar-brand {
    display: block;
    color: #fff;
    padding: 0 12px 28px;
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
    display: flex;
    align-items: center;
    min-height: 43px;
    padding: 10px 12px;
    border-radius: 9px;
    color: #dce6dd;
    font-size: 13px;
    font-weight: 600;
    transition: background .2s, color .2s;
}

.sidebar-nav a:hover {
    background: rgba(255,255,255,.09);
    color: #fff;
    text-decoration: none;
}

.sidebar-nav a.active {
    background: #eaf1e9;
    color: var(--green-dark);
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
    margin-bottom: 3px;
}

.workspace {
    min-width: 0;
    flex: 1;
}

.topbar {
    min-height: 76px;
    background: #fff;
    border-bottom: 1px solid var(--line);
    padding: 14px 34px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.topbar-title {
    font-size: 13px;
    color: var(--muted);
}

.topbar-title strong {
    display: block;
    color: var(--ink);
    font-size: 16px;
    margin-bottom: 2px;
}

.top-actions {
    display: flex;
    align-items: center;
    gap: 12px;
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
    font-weight: 800;
}

.inline-form { display: inline; }

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

.btn.small { padding: 7px 10px; font-size: 12px; }

.wrap {
    width: min(1440px, 100%);
    margin: 0 auto;
    padding: 34px;
    min-height: calc(100vh - 76px);
}

.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.eyebrow {
    font-size: 11px;
    letter-spacing: .13em;
    text-transform: uppercase;
    color: var(--gold);
    font-weight: 700;
}

.page-head h1 {
    margin: 4px 0;
    font-size: 30px;
    letter-spacing: -.03em;
}

.muted { color: var(--muted); }

.card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 18px;
    box-shadow: 0 4px 18px rgba(30,55,40,.035);
}

.btn:focus,
button:focus,
input:focus,
select:focus,
textarea:focus {
    outline: 3px solid #b9d4bf;
    outline-offset: 1px;
}

.toolbar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}

.field label {
    font-size: 13px;
    font-weight: 700;
}

.field input,
.field select,
.field textarea,
.toolbar input,
.toolbar select {
    width: 100%;
    border: 1px solid #d7e0d6;
    border-radius: 8px;
    padding: 11px 12px;
    background: #fff;
    color: var(--ink);
    font: inherit;
}

.field textarea { min-height: 100px; resize: vertical; }
.toolbar input { width: min(340px, 100%); }

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 18px;
}

.span-2 { grid-column: 1 / -1; }

.table-wrap { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 680px;
}

th, td {
    text-align: left;
    padding: 13px 12px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
}

th {
    font-size: 11px;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
}

td { font-size: 14px; }

.pill {
    display: inline-block;
    border-radius: 99px;
    padding: 4px 9px;
    background: var(--green-soft);
    color: var(--green);
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
}

.pill.urgent, .pill.lost {
    background: #fae9e6;
    color: var(--red);
}

.pill.high, .pill.quoted {
    background: #f7f0df;
    color: #80602a;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.stat {
    background: #f8faf7;
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 15px;
}

.stat strong { display: block; font-size: 22px; }

.error {
    background: #fff0ed;
    border: 1px solid #f2d0c9;
    color: #963b30;
    padding: 12px 14px;
    border-radius: 9px;
    margin-bottom: 18px;
}

.notice {
    background: #edf5ed;
    border: 1px solid #d6e7d7;
    padding: 12px 14px;
    border-radius: 9px;
    margin-bottom: 18px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.detail label {
    display: block;
    color: var(--muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.detail strong {
    display: block;
    margin-top: 4px;
    overflow-wrap: anywhere;
}

.pagination {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 18px;
}

.pagination a, .pagination span {
    padding: 7px 11px;
    border: 1px solid var(--line);
    border-radius: 7px;
    background: #fff;
}

.footer {
    color: var(--muted);
    font-size: 12px;
    text-align: center;
    padding: 20px;
}

.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.section-title h2 { font-size: 18px; margin: 0 0 14px; }

@media (max-width: 900px) {
    .sidebar {
        width: 210px;
        flex-basis: 210px;
    }
    .wrap { padding: 26px 22px; }
    .topbar { padding: 14px 22px; }
}

@media (max-width: 700px) {
    .app-shell { display: block; }

    .sidebar {
        width: 100%;
        min-height: auto;
        padding: 14px;
    }

    .sidebar-brand {
        padding: 2px 7px 12px;
    }

    .sidebar-brand strong { font-size: 15px; }
    .sidebar-label { padding: 13px 7px 7px; }

    .sidebar-nav {
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 5px;
    }

    .sidebar-nav a {
        flex: 0 0 auto;
        min-height: 38px;
        white-space: nowrap;
        padding: 8px 11px;
    }

    .sidebar-bottom { display: none; }

    .topbar {
        min-height: 62px;
        padding: 12px 16px;
    }

    .topbar-title { display: none; }
    .top-actions { margin-left: auto; }
    .user-chip { font-size: 12px; }

    .wrap {
        width: 100%;
        padding: 22px 14px;
    }

    .page-head h1 { font-size: 25px; }
    .card { padding: 16px; }
    .grid, .detail-grid { grid-template-columns: 1fr; }
    .span-2 { grid-column: auto; }
    .stats { grid-template-columns: 1fr; }
}
</style>
</head>

<body>
<div class="app-shell">

<aside class="sidebar">
    <a class="sidebar-brand" href="<?= nh_e($base) ?>/admin">
        <strong>NATURE HOLIDAYS</strong>
        <span>ADMIN WORKSPACE</span>
    </a>

    <div class="sidebar-label">Workspace</div>

    <nav class="sidebar-nav">
        <?php foreach ($navItems as [$label, $path, $permission]): ?>
            <?php if ($permission === null || Auth::can($permission)): ?>
                <a
                    href="<?= nh_e($base . $path) ?>"
                    class="<?= nh_nav_active($path, $currentPath) ? 'active' : '' ?>"
                    <?= nh_nav_active($path, $currentPath) ? 'aria-current="page"' : '' ?>
                >
                    <?= nh_e($label) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-bottom">
        <strong><?= nh_e($userName) ?></strong>
        Nature Holidays · Internal workspace
    </div>
</aside>

<div class="workspace">
    <header class="topbar">
        <div class="topbar-title">
            <strong><?= nh_e($pageTitle ?? 'Workspace') ?></strong>
            Manage your travel business
        </div>

        <div class="top-actions">
            <div class="user-chip">
                <span class="user-avatar"><?= nh_e($initials) ?></span>
                <?= nh_e($userName) ?>
            </div>

            <form class="inline-form" method="post" action="<?= nh_e($base) ?>/logout">
                <input type="hidden" name="_csrf" value="<?= nh_e(Csrf::token()) ?>">
                <button class="btn secondary small" type="submit">Sign out</button>
            </form>
        </div>
    </header>

    <main class="wrap">
