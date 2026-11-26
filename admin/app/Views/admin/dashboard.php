
<?php
use App\Core\Auth;
use App\Core\Csrf;

$userName = (string)($user['name'] ?? 'Admin');
$userRole = (string)($user['role'] ?? 'Administrator');

$modules = [
    [
        'permission' => 'customers.view',
        'icon' => '♙',
        'title' => 'Customers',
        'description' => 'Customer records, profiles and relationships.',
        'url' => '/admin/customers',
        'tag' => 'CRM'
    ],
    [
        'permission' => 'enquiries.view',
        'icon' => '✉',
        'title' => 'Enquiries & CRM',
        'description' => 'Track incoming travel enquiries and leads.',
        'url' => '/admin/enquiries',
        'tag' => 'Sales'
    ],
    [
        'permission' => 'packages.manage',
        'icon' => '◇',
        'title' => 'Packages',
        'description' => 'Create and manage travel experiences.',
        'url' => '/admin/packages',
        'tag' => 'Catalogue'
    ],
    [
        'permission' => 'operations.view',
        'icon' => '⌖',
        'title' => 'Operations',
        'description' => 'Destinations, suppliers, bookings and itineraries.',
        'url' => '/admin/operations',
        'tag' => 'Travel'
    ],
    [
        'permission' => 'payments.view',
        'icon' => '₹',
        'title' => 'Finance',
        'description' => 'Payment records and transaction tracking.',
        'url' => '/admin/payments',
        'tag' => 'Finance'
    ],
    [
        'permission' => 'reports.view',
        'icon' => '▤',
        'title' => 'Management Centre',
        'description' => 'Reports, staff, permissions and business settings.',
        'url' => '/admin/management',
        'tag' => 'Admin'
    ],
    [
        'permission' => 'cms.manage',
        'icon' => '✎',
        'title' => 'Website Content',
        'description' => 'Manage the content displayed on your website.',
        'url' => '/admin/management/content',
        'tag' => 'Website'
    ],
];

$visibleModules = array_values(array_filter(
    $modules,
    fn($module) => Auth::can($module['permission'])
));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>Dashboard · Nature Holidays</title>

<style>
:root {
    --ink: #20372b;
    --muted: #718076;
    --green: #28543b;
    --green-dark: #1d402d;
    --green-soft: #eaf1e9;
    --line: #e3e9e2;
    --paper: #f5f7f3;
    --white: #fff;
    --gold: #b58c4c;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    background: var(--paper);
    color: var(--ink);
    font: 14px/1.5 Arial, sans-serif;
}

a { color: inherit; text-decoration: none; }

.app {
    min-height: 100vh;
    display: flex;
}

/* SIDEBAR */
.sidebar {
    width: 250px;
    flex-shrink: 0;
    background: var(--green-dark);
    color: #fff;
    padding: 27px 18px;
    display: flex;
    flex-direction: column;
}

.brand {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0 10px 32px;
    border-bottom: 1px solid rgba(255,255,255,.13);
}

.brand-mark {
    width: 39px;
    height: 39px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: rgba(255,255,255,.13);
    font-size: 21px;
}

.brand strong {
    display: block;
    font-size: 14px;
    letter-spacing: .02em;
}

.brand small {
    color: #b8cbbd;
    font-size: 11px;
}

.nav-label {
    margin: 28px 10px 10px;
    color: #a9bdae;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.side-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    margin: 3px 0;
    border-radius: 9px;
    color: #e1eae3;
    font-size: 13px;
    transition: .2s ease;
}

.side-link:hover,
.side-link.active {
    background: rgba(255,255,255,.13);
    color: #fff;
}

.side-icon {
    width: 22px;
    text-align: center;
    font-size: 17px;
}

.sidebar-bottom {
    margin-top: auto;
    padding: 17px 10px 0;
    border-top: 1px solid rgba(255,255,255,.13);
    color: #b8cbbd;
    font-size: 11px;
}

/* MAIN */
.main {
    flex: 1;
    min-width: 0;
}

.topbar {
    height: 72px;
    background: var(--white);
    border-bottom: 1px solid var(--line);
    padding: 0 38px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.breadcrumb {
    color: var(--muted);
    font-size: 12px;
}

.breadcrumb strong { color: var(--ink); }

.top-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--green-soft);
    color: var(--green);
    font-weight: 700;
}

.user-copy strong {
    display: block;
    font-size: 12px;
}

.user-copy small {
    color: var(--muted);
    font-size: 11px;
}

.logout button {
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #fff;
    color: var(--green);
    padding: 9px 13px;
    font-weight: 700;
    cursor: pointer;
}

.logout button:hover {
    background: var(--green-soft);
}

.content {
    width: min(1440px, 100%);
    margin: auto;
    padding: 38px;
}

.eyebrow {
    color: var(--gold);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .15em;
    text-transform: uppercase;
}

.heading-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 30px;
}

h1 {
    margin: 7px 0 5px;
    font-size: clamp(25px, 3vw, 34px);
    letter-spacing: -.04em;
}

.subtitle {
    margin: 0;
    color: var(--muted);
}

.date-note {
    color: var(--muted);
    font-size: 12px;
}

/* WELCOME PANEL */
.welcome {
    position: relative;
    overflow: hidden;
    background: var(--green);
    color: #fff;
    border-radius: 16px;
    padding: 30px 32px;
    margin-bottom: 30px;
}

.welcome:after {
    content: "";
    position: absolute;
    width: 230px;
    height: 230px;
    right: -55px;
    top: -115px;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 50%;
    box-shadow: 0 0 0 28px rgba(255,255,255,.035),
                0 0 0 58px rgba(255,255,255,.025);
}

.welcome .eyebrow { color: #d8c49e; }

.welcome h2 {
    margin: 7px 0;
    font-size: 23px;
    letter-spacing: -.02em;
}

.welcome p {
    margin: 0;
    color: #dce8df;
    font-size: 13px;
}

/* MODULES */
.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.section-heading h2 {
    margin: 0;
    font-size: 17px;
}

.section-heading span {
    color: var(--muted);
    font-size: 12px;
}

.modules {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.module {
    min-height: 175px;
    padding: 21px;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 13px;
    display: flex;
    flex-direction: column;
    transition: transform .2s ease, box-shadow .2s ease,
                border-color .2s ease;
}

.module:hover {
    transform: translateY(-3px);
    border-color: #b7cbb9;
    box-shadow: 0 12px 26px rgba(32,55,43,.07);
}

.module-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-icon {
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    border-radius: 11px;
    background: var(--green-soft);
    color: var(--green);
    font-size: 20px;
}

.module-tag {
    color: var(--muted);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.module h3 {
    margin: 19px 0 5px;
    font-size: 15px;
}

.module p {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.6;
}

.module-arrow {
    margin-top: auto;
    padding-top: 14px;
    color: var(--green);
    font-size: 12px;
    font-weight: 700;
}

/* FOOTER */
.footer {
    padding: 30px 0 5px;
    color: var(--muted);
    font-size: 11px;
    text-align: center;
}

/* RESPONSIVE */
@media (max-width: 1050px) {
    .modules { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sidebar { width: 220px; }
    .content { padding: 28px; }
}

@media (max-width: 700px) {
    .app { display: block; }

    .sidebar {
        width: 100%;
        padding: 14px 16px;
    }

    .brand {
        padding: 0 4px 13px;
    }

    .nav-label { display: none; }

    .sidebar nav {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-top: 12px;
    }

    .side-link {
        flex-shrink: 0;
        padding: 9px 11px;
        margin: 0;
        font-size: 11px;
    }

    .side-icon { display: none; }
    .sidebar-bottom { display: none; }

    .topbar {
        height: 60px;
        padding: 0 17px;
    }

    .user-copy { display: none; }

    .content { padding: 25px 17px; }

    .heading-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .welcome { padding: 24px; }

    .modules { grid-template-columns: 1fr; }

    .module { min-height: 155px; }
}
</style>
</head>

<body>
<div class="app">

<aside class="sidebar">
    <a class="brand" href="<?= htmlspecialchars(APP_BASE_PATH . '/admin', ENT_QUOTES, 'UTF-8') ?>">
        <span class="brand-mark">✦</span>
        <span>
            <strong>NATURE HOLIDAYS</strong>
            <small>Management workspace</small>
        </span>
    </a>

    <div class="nav-label">Workspace</div>

    <nav>
        <a class="side-link active" href="<?= APP_BASE_PATH ?>/admin">
            <span class="side-icon">⌂</span> Dashboard
        </a>

        <?php if (Auth::can('customers.view')): ?>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/customers">
            <span class="side-icon">♙</span> Customers
        </a>
        <?php endif; ?>

        <?php if (Auth::can('enquiries.view')): ?>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/enquiries">
            <span class="side-icon">✉</span> Enquiries & CRM
        </a>
        <?php endif; ?>

        <?php if (Auth::can('packages.manage')): ?>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/packages">
            <span class="side-icon">◇</span> Packages
        </a>
        <?php endif; ?>

        <?php if (Auth::can('operations.view')): ?>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/operations">
            <span class="side-icon">⌖</span> Operations
        </a>
        <?php endif; ?>

        <?php if (Auth::can('payments.view')): ?>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/payments">
            <span class="side-icon">₹</span> Finance
        </a>
        <?php endif; ?>

        <?php if (Auth::can('reports.view')): ?>
        <div class="nav-label">Administration</div>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/management">
            <span class="side-icon">▤</span> Management Centre
        </a>
        <?php endif; ?>

        <?php if (Auth::can('cms.manage')): ?>
        <a class="side-link" href="<?= APP_BASE_PATH ?>/admin/management/content">
            <span class="side-icon">✎</span> Website Content
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-bottom">
        NATURE HOLIDAYS<br>
        Internal business workspace
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="breadcrumb">Workspace <span> / </span> <strong>Dashboard</strong></div>

        <div class="top-user">
            <div class="avatar">
                <?= htmlspecialchars(strtoupper(substr($userName, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="user-copy">
                <strong><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></strong>
                <small><?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8') ?></small>
            </div>
            <form class="logout" method="POST" action="<?= APP_BASE_PATH ?>/logout">
                <input type="hidden" name="_csrf"
                    value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Sign out</button>
            </form>
        </div>
    </header>

    <main class="content">
        <div class="heading-row">
            <div>
                <div class="eyebrow">Your workspace</div>
                <h1>Good to have you back.</h1>
                <p class="subtitle">Everything you need to manage your travel business, in one place.</p>
            </div>
            <div class="date-note">Nature Holidays · Admin</div>
        </div>

        <section class="welcome">
            <div class="eyebrow">Management overview</div>
            <h2>Welcome, <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>.</h2>
            <p>Your workspace is ready. Choose a department to get started.</p>
        </section>

        <div class="section-heading">
            <h2>Your workspace</h2>
            <span><?= count($visibleModules) ?> available sections</span>
        </div>

        <section class="modules">
            <?php foreach ($visibleModules as $module): ?>
            <a class="module" href="<?= APP_BASE_PATH . $module['url'] ?>">
                <div class="module-top">
                    <span class="module-icon"><?= htmlspecialchars($module['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="module-tag"><?= htmlspecialchars($module['tag'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h3><?= htmlspecialchars($module['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($module['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="module-arrow">Open section →</div>
            </a>
            <?php endforeach; ?>
        </section>

        <footer class="footer">
            Nature Holidays · Internal business workspace
        </footer>
    </main>
</div>
</div>
</body>
</html>
