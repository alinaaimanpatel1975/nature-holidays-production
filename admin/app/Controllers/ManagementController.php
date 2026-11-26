<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use PDO;
use Throwable;

final class ManagementController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    private function guard(string $p): void
    {
        Auth::requireLogin();

        if (!Auth::can($p)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    private function base(): string
    {
        return rtrim((string)APP_BASE_PATH, '/');
    }

    private function token(): string
    {
        return htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8');
    }

    private function verify(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Refresh and retry.');
        }
    }

    private function v(string $k, int $n = 4000): string
    {
        $s = trim((string)($_POST[$k] ?? ''));

        return function_exists('mb_substr')
            ? mb_substr($s, 0, $n)
            : substr($s, 0, $n);
    }

    private function id(): int
    {
        return max(0, (int)($_POST['id'] ?? $_GET['id'] ?? 0));
    }

    private function go(string $p): never
    {
        header('Location: ' . $this->base() . $p);
        exit;
    }

    private function logAudit(
        string $action,
        string $type,
        int $id,
        array $d = []
    ): void {
        try {
            $s = $this->db->prepare(
                'INSERT INTO audit_logs
                (user_id,action,entity_type,entity_id,ip_address,details,created_at)
                VALUES(?,?,?,?,?,?,NOW())'
            );

            $s->execute([
                (int)(Auth::user()['id'] ?? 0) ?: null,
                $action,
                $type,
                $id,
                $_SERVER['REMOTE_ADDR'] ?? null,
                json_encode($d)
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Shared Management Centre page renderer.
     * Uses the common Operations/Management layout.
     */
    private function page(string $title, string $body): void
    {
        $base = $this->base();

        $content =
            '<section class="admin-module mg">
                <div class="page-head">
                    <div>
                        <div class="eyebrow">MANAGEMENT CENTRE</div>
                        <h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>
                    </div>
                </div>
                ' . $body . '
            </section>';

        $this->view('operations/page', [
            'title'   => $title,
            'content' => $content,
            'base'    => $base,
            'section' => 'management'
        ]);
    }

    private function table(array $heads, array $rows): string
    {
        $h = '<div class="panel"><table><thead><tr>';

        foreach ($heads as $x) {
            $h .= '<th>' . htmlspecialchars($x, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        $h .= '</tr></thead><tbody>';

        foreach ($rows as $r) {
            $h .= '<tr>';

            foreach ($r as $c) {
                $h .= '<td>' . $c . '</td>';
            }

            $h .= '</tr>';
        }

        $h .= '</tbody></table></div>';

        return $h;
    }

    private function form(
        string $url,
        array $fields,
        string $button = 'Save'
    ): string {
        $h = '<form method="post" action="' . $this->base() . $url . '">
            <input type="hidden" name="_csrf" value="' . $this->token() . '">';

        foreach ($fields as $f) {
            [$k, $label, $value, $type] = $f;

            if ($type === 'hidden') {
                $h .= '<input type="hidden" name="' . $k . '" value="' .
                    htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '">';
                continue;
            }

            $h .= '<label>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';

            if ($type === 'textarea') {
                $h .= '<textarea name="' . $k . '" rows="5">' .
                    htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') .
                    '</textarea>';
            } else {
                $h .= '<input type="' . $type . '" name="' . $k . '" value="' .
                    htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '">';
            }
        }

        $h .= '<div class="bar"><button>' . $button . '</button></div></form>';

        return $h;
    }

    public function dashboard(): void
    {
        $this->guard('reports.view');

        $stats = [];

        foreach ([
            'users' => 'Staff',
            'customers' => 'Customers',
            'enquiries' => 'Enquiries',
            'packages' => 'Packages',
            'quotations' => 'Quotations',
            'bookings' => 'Bookings',
            'payment_transactions' => 'Payment records'
        ] as $t => $label) {
            try {
                $n = (int)$this->db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            } catch (Throwable) {
                $n = 0;
            }

            $stats[] = '<div class="card">' . $label .
                '<div class="num">' . $n . '</div></div>';
        }

        $links = [
            ['/admin/management/reports', 'Reports'],
            ['/admin/management/staff', 'Staff & access'],
            ['/admin/management/roles', 'Roles & permissions'],
            ['/admin/management/audit', 'Audit trail'],
            ['/admin/management/settings', 'Business settings'],
            ['/admin/management/content', 'Website content']
        ];

        $b = '<div class="grid">' . implode('', $stats) . '</div><div class="bar">';

        foreach ($links as [$u, $l]) {
            $b .= '<a class="btn" href="' . $this->base() . $u . '">' . $l . '</a>';
        }

        $this->page('Management centre', $b . '</div>');
    }

    public function reports(): void
    {
        $this->guard('reports.view');

        $queries = [
            'New enquiries (all time)' => "SELECT COUNT(*) FROM enquiries WHERE status='new'",
            'Confirmed bookings' => "SELECT COUNT(*) FROM bookings WHERE status='confirmed'",
            'Cancelled bookings' => "SELECT COUNT(*) FROM bookings WHERE status='cancelled'",
            'Active customers' => "SELECT COUNT(*) FROM customers",
            'Completed payments' => "SELECT COUNT(*) FROM payment_transactions WHERE status='completed'"
        ];

        $b = '<div class="grid">';

        foreach ($queries as $label => $sql) {
            try {
                $n = (int)$this->db->query($sql)->fetchColumn();
            } catch (Throwable) {
                $n = '—';
            }

            $b .= '<div class="card">' . htmlspecialchars($label) .
                '<div class="num">' . $n . '</div></div>';
        }

        $b .= '</div><div class="panel"><h2>Recent bookings</h2>';

        try {
            $rows = $this->db->query(
                'SELECT booking_reference,status,travel_start_date,total_amount,currency,created_at
                FROM bookings ORDER BY created_at DESC LIMIT 20'
            )->fetchAll(PDO::FETCH_ASSOC);

            $b .= $this->table(
                ['Reference', 'Status', 'Travel date', 'Total', 'Created'],
                array_map(
                    fn($r) => [
                        htmlspecialchars((string)$r['booking_reference']),
                        htmlspecialchars((string)$r['status']),
                        htmlspecialchars((string)$r['travel_start_date']),
                        htmlspecialchars((string)$r['currency'] . ' ' . (string)$r['total_amount']),
                        htmlspecialchars((string)$r['created_at'])
                    ],
                    $rows
                )
            );
        } catch (Throwable) {
            $b .= '<p class="muted">Booking report unavailable until Operations tables are present.</p>';
        }

        $this->page('Reports overview', $b . '</div>');
    }

    public function staff(): void
    {
        $this->guard('settings.manage');

        $rows = $this->db->query(
            'SELECT u.id,u.name,u.email,u.phone,u.status,u.last_login_at,r.name role_name
            FROM users u LEFT JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC'
        )->fetchAll(PDO::FETCH_ASSOC);

        $out = [];

        foreach ($rows as $r) {
            $id = (int)$r['id'];

            $actions =
                '<a href="' . $this->base() . '/admin/management/staff/edit?id=' . $id . '">Edit</a>
                <form class="inline" method="post" action="' . $this->base() . '/admin/management/staff/toggle">
                    <input type="hidden" name="_csrf" value="' . $this->token() . '">
                    <input type="hidden" name="id" value="' . $id . '">
                    <button>' . ($r['status'] === 'active' ? 'Deactivate' : 'Activate') . '</button>
                </form>';

            if ($id !== (int)(Auth::user()['id'] ?? 0)) {
                $actions .=
                    ' <form class="inline" method="post" action="' . $this->base() . '/admin/management/staff/delete"
                    onsubmit="return confirm(\'Permanently delete this staff login? This cannot be undone.\')">
                        <input type="hidden" name="_csrf" value="' . $this->token() . '">
                        <input type="hidden" name="id" value="' . $id . '">
                        <button class="danger">Delete</button>
                    </form>';
            }

            $out[] = [
                htmlspecialchars($r['name']),
                htmlspecialchars($r['email']),
                htmlspecialchars((string)$r['role_name']),
                htmlspecialchars($r['status']),
                htmlspecialchars((string)$r['last_login_at']),
                $actions
            ];
        }

        $b =
            '<div class="bar">
                <a class="btn" href="' . $this->base() . '/admin/management/staff/create">Add staff user</a>
            </div>' .
            $this->table(
                ['Name', 'Email', 'Role', 'Status', 'Last login', 'Actions'],
                $out
            );

        $this->page('Staff accounts', $b);
    }

    public function staffCreate(): void
    {
        $this->guard('settings.manage');

        $roles = $this->db->query(
            'SELECT id,name FROM roles ORDER BY name'
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $opts =
            '<form method="post" action="' . $this->base() . '/admin/management/staff/store">
                <input type="hidden" name="_csrf" value="' . $this->token() . '">
                <label>Name</label><input name="name" required>
                <label>Email</label><input type="email" name="email" required>
                <label>Phone</label><input name="phone">
                <label>Temporary password</label><input type="password" name="password" minlength="12" required>
                <label>Role</label><select name="role_id">';

        foreach ($roles as $id => $name) {
            $opts .= '<option value="' . (int)$id . '">' .
                htmlspecialchars($name) . '</option>';
        }

        $this->page(
            'Add staff user',
            $opts . '</select>
                <p class="hint">Use a unique temporary password and share it securely. The user should change it after first login.</p>
                <button>Create user</button>
            </form>'
        );
    }

    public function staffStore(): void
    {
        $this->guard('settings.manage');
        $this->verify();

        $name = $this->v('name', 150);
        $email = strtolower($this->v('email', 190));
        $pass = (string)($_POST['password'] ?? '');
        $role = (int)($_POST['role_id'] ?? 0);

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 12) {
            exit('Check name, email and password (minimum 12 characters).');
        }

        $q = $this->db->prepare(
            'INSERT INTO users(role_id,name,email,password,phone,status,created_at,updated_at)
            VALUES(?,?,?,?,?,?,NOW(),NOW())'
        );

        $q->execute([
            $role,
            $name,
            $email,
            password_hash($pass, PASSWORD_DEFAULT),
            $this->v('phone', 40),
            'active'
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->logAudit('create', 'user', $id, ['email' => $email]);
        $this->go('/admin/management/staff');
    }

    public function staffEdit(): void
    {
        $this->guard('settings.manage');

        $q = $this->db->prepare(
            'SELECT id,name,email,phone,role_id FROM users WHERE id=?'
        );

        $q->execute([(int)($_GET['id'] ?? 0)]);
        $u = $q->fetch(PDO::FETCH_ASSOC);

        if (!$u) {
            exit('User not found');
        }

        $roles = $this->db->query(
            'SELECT id,name FROM roles ORDER BY name'
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $b =
            '<form method="post" action="' . $this->base() . '/admin/management/staff/update">
                <input type="hidden" name="_csrf" value="' . $this->token() . '">
                <input type="hidden" name="id" value="' . (int)$u['id'] . '">';

        foreach ([
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone'
        ] as $k => $l) {
            $b .= '<label>' . $l . '</label>
                <input name="' . $k . '" value="' .
                htmlspecialchars((string)$u[$k], ENT_QUOTES, 'UTF-8') . '">';
        }

        $b .= '<label>Role</label><select name="role_id">';

        foreach ($roles as $id => $n) {
            $b .= '<option value="' . (int)$id . '"' .
                ((int)$id === (int)$u['role_id'] ? ' selected' : '') . '>' .
                htmlspecialchars($n) . '</option>';
        }

        $b .= '</select>
            <label>New password (leave blank to keep current)</label>
            <input type="password" name="password" minlength="12">
            <button>Save changes</button>
            </form>';

        $this->page('Edit staff user', $b);
    }

    public function staffUpdate(): void
    {
        $this->guard('settings.manage');
        $this->verify();

        $id = $this->id();
        $name = $this->v('name', 150);
        $email = strtolower($this->v('email', 190));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
            exit('Invalid name or email.');
        }

        $q = $this->db->prepare(
            'UPDATE users SET name=?,email=?,phone=?,role_id=?,updated_at=NOW() WHERE id=?'
        );

        $q->execute([
            $name,
            $email,
            $this->v('phone', 40),
            (int)($_POST['role_id'] ?? 0),
            $id
        ]);

        if ((string)($_POST['password'] ?? '') !== '') {
            $p = (string)$_POST['password'];

            if (strlen($p) < 12) {
                exit('Password must be at least 12 characters.');
            }

            $q = $this->db->prepare('UPDATE users SET password=? WHERE id=?');
            $q->execute([password_hash($p, PASSWORD_DEFAULT), $id]);
        }

        $this->logAudit('update', 'user', $id);
        $this->go('/admin/management/staff');
    }

    public function staffToggle(): void
    {
        $this->guard('settings.manage');
        $this->verify();

        $id = $this->id();

        if ($id === (int)(Auth::user()['id'] ?? 0)) {
            exit('You cannot deactivate your own account.');
        }

        $q = $this->db->prepare(
            "UPDATE users SET status=IF(status='active','inactive','active'),updated_at=NOW() WHERE id=?"
        );

        $q->execute([$id]);

        $this->logAudit('toggle', 'user', $id);
        $this->go('/admin/management/staff');
    }

    public function staffDelete(): void
    {
        $this->guard('settings.manage');
        $this->verify();

        $id = $this->id();

        if ($id === (int)(Auth::user()['id'] ?? 0)) {
            exit('You cannot delete your own account.');
        }

        $q = $this->db->prepare('SELECT COUNT(*) FROM audit_logs WHERE user_id=?');
        $q->execute([$id]);

        if ((int)$q->fetchColumn() > 0) {
            $this->staffToggleSafe($id);
            $_SESSION['mg_notice'] = 'User has audit history; account deactivated instead of deleted.';
            $this->go('/admin/management/staff');
        }

        $q = $this->db->prepare('DELETE FROM users WHERE id=?');
        $q->execute([$id]);

        $this->logAudit('delete', 'user', $id);
        $this->go('/admin/management/staff');
    }

    private function staffToggleSafe(int $id): void
    {
        $q = $this->db->prepare(
            "UPDATE users SET status='inactive',updated_at=NOW() WHERE id=?"
        );

        $q->execute([$id]);
    }

    public function roles(): void
    {
        $this->guard('settings.manage');

        $roles = $this->db->query(
            'SELECT id,name,description FROM roles ORDER BY name'
        )->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];

        foreach ($roles as $r) {
            $id = (int)$r['id'];

            $c = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role_id=?');
            $c->execute([$id]);

            $rows[] = [
                htmlspecialchars($r['name']),
                htmlspecialchars((string)$r['description']),
                (int)$c->fetchColumn()
            ];
        }

        $this->page(
            'Roles & permissions',
            $this->table(
                ['Role', 'Description', 'Assigned users'],
                $rows
            ) .
            '<p class="hint">Role creation and permission matrix editing are intentionally not exposed here; permission changes should be reviewed to avoid accidentally locking out administrators.</p>'
        );
    }

    public function audit(): void
    {
        $this->guard('audit.view');

        $rows = $this->db->query(
            'SELECT a.id,a.action,a.entity_type,a.entity_id,a.ip_address,a.details,a.created_at,u.name
            FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id
            ORDER BY a.id DESC LIMIT 200'
        )->fetchAll(PDO::FETCH_ASSOC);

        $out = [];

        foreach ($rows as $r) {
            $out[] = [
                (int)$r['id'],
                htmlspecialchars((string)$r['created_at']),
                htmlspecialchars((string)$r['name']),
                htmlspecialchars((string)$r['action']),
                htmlspecialchars((string)$r['entity_type'] . ' #' . $r['entity_id']),
                htmlspecialchars((string)$r['ip_address']),
                '<code>' . htmlspecialchars((string)$r['details']) . '</code>'
            ];
        }

        $this->page(
            'Audit trail · latest 200',
            $this->table(
                ['ID', 'When', 'User', 'Action', 'Record', 'IP', 'Details'],
                $out
            )
        );
    }

    public function settings(): void
    {
        $this->guard('settings.manage');

        $rows = $this->db->query(
            'SELECT setting_key,setting_value,updated_at FROM app_settings ORDER BY setting_key'
        )->fetchAll(PDO::FETCH_ASSOC);

        $out = [];

        foreach ($rows as $r) {
            $out[] = [
                htmlspecialchars($r['setting_key']),
                htmlspecialchars((string)$r['setting_value']),
                htmlspecialchars((string)$r['updated_at']),
                '<form class="inline" method="post" action="' . $this->base() . '/admin/management/settings/delete"
                onsubmit="return confirm(\'Delete this setting?\')">
                    <input type="hidden" name="_csrf" value="' . $this->token() . '">
                    <input type="hidden" name="key" value="' .
                    htmlspecialchars($r['setting_key'], ENT_QUOTES, 'UTF-8') . '">
                    <button class="danger">Delete</button>
                </form>'
            ];
        }

        $b =
            $this->table(
                ['Key', 'Value', 'Updated', 'Actions'],
                $out
            ) .
            '<div class="panel">
                <h2>Add / update setting</h2>
                <form method="post" action="' . $this->base() . '/admin/management/settings/save">
                    <input type="hidden" name="_csrf" value="' . $this->token() . '">
                    <label>Key (lowercase letters, numbers, underscore)</label>
                    <input name="setting_key" pattern="[a-z0-9_]+" required>
                    <label>Value</label>
                    <textarea name="setting_value" rows="3"></textarea>
                    <button>Save setting</button>
                </form>
            </div>
            <p class="hint">Do not store passwords, API secrets, payment credentials or personal customer data here.</p>';

        $this->page('Business settings', $b);
    }

    public function settingSave(): void
    {
        $this->guard('settings.manage');
        $this->verify();

        $key = $this->v('setting_key', 100);

        if (!preg_match('/^[a-z0-9_]+$/', $key)) {
            exit('Invalid key.');
        }

        $q = $this->db->prepare(
            'INSERT INTO app_settings(setting_key,setting_value,updated_at)
            VALUES(?,?,NOW())
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),updated_at=NOW()'
        );

        $q->execute([$key, $this->v('setting_value', 5000)]);

        $this->logAudit('upsert', 'setting', 0, ['key' => $key]);
        $this->go('/admin/management/settings');
    }

    public function settingDelete(): void
    {
        $this->guard('settings.manage');
        $this->verify();

        $key = $this->v('key', 100);

        $q = $this->db->prepare('DELETE FROM app_settings WHERE setting_key=?');
        $q->execute([$key]);

        $this->logAudit('delete', 'setting', 0, ['key' => $key]);
        $this->go('/admin/management/settings');
    }

    public function content(): void
    {
        $this->guard('cms.manage');

        $rows = $this->db->query(
            'SELECT id,title,slug,status,updated_at FROM cms_pages ORDER BY updated_at DESC'
        )->fetchAll(PDO::FETCH_ASSOC);

        $out = [];

        foreach ($rows as $r) {
            $id = (int)$r['id'];

            $out[] = [
                htmlspecialchars($r['title']),
                htmlspecialchars($r['slug']),
                htmlspecialchars($r['status']),
                htmlspecialchars($r['updated_at']),
                '<a href="' . $this->base() . '/admin/management/content/edit?id=' . $id . '">Edit</a>
                <form class="inline" method="post" action="' . $this->base() . '/admin/management/content/delete"
                onsubmit="return confirm(\'Delete this page?\')">
                    <input type="hidden" name="_csrf" value="' . $this->token() . '">
                    <input type="hidden" name="id" value="' . $id . '">
                    <button class="danger">Delete</button>
                </form>'
            ];
        }

        $this->page(
            'Website content pages',
            $this->form(
                '/admin/management/content/store',
                [
                    ['title', 'Page title', '', 'text'],
                    ['slug', 'URL slug', '', 'text'],
                    ['body', 'Page content (HTML or plain text)', '', 'textarea']
                ],
                'Create page'
            ) .
            $this->table(
                ['Title', 'Slug', 'Status', 'Updated', 'Actions'],
                $out
            )
        );
    }

    public function contentStore(): void
    {
        $this->guard('cms.manage');
        $this->verify();

        $title = $this->v('title', 180);
        $slug = strtolower($this->v('slug', 180));

        if ($title === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            exit('Enter a title and valid URL slug.');
        }

        $q = $this->db->prepare(
            "INSERT INTO cms_pages(title,slug,body,status,created_at,updated_at)
            VALUES(?,?,?,'draft',NOW(),NOW())"
        );

        $q->execute([$title, $slug, $this->v('body', 20000)]);

        $this->logAudit('create', 'cms_page', (int)$this->db->lastInsertId());
        $this->go('/admin/management/content');
    }

    public function contentEdit(): void
    {
        $this->guard('cms.manage');

        $q = $this->db->prepare('SELECT * FROM cms_pages WHERE id=?');
        $q->execute([(int)($_GET['id'] ?? 0)]);

        $r = $q->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            exit('Page not found');
        }

        $b =
            '<form method="post" action="' . $this->base() . '/admin/management/content/update">
                <input type="hidden" name="_csrf" value="' . $this->token() . '">
                <input type="hidden" name="id" value="' . (int)$r['id'] . '">

                <label>Title</label>
                <input name="title" value="' .
                    htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') . '">

                <label>Slug</label>
                <input name="slug" value="' .
                    htmlspecialchars($r['slug'], ENT_QUOTES, 'UTF-8') . '">

                <label>Content</label>
                <textarea name="body" rows="12">' .
                    htmlspecialchars($r['body'], ENT_QUOTES, 'UTF-8') . '</textarea>

                <label>Status</label>
                <select name="status">
                    <option value="draft"' . ($r['status'] === 'draft' ? ' selected' : '') . '>Draft</option>
                    <option value="published"' . ($r['status'] === 'published' ? ' selected' : '') . '>Published</option>
                </select>

                <button>Save page</button>
            </form>';

        $this->page('Edit website page', $b);
    }

    public function contentUpdate(): void
    {
        $this->guard('cms.manage');
        $this->verify();

        $id = $this->id();
        $slug = strtolower($this->v('slug', 180));

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            exit('Invalid slug.');
        }

        $q = $this->db->prepare(
            'UPDATE cms_pages SET title=?,slug=?,body=?,status=?,updated_at=NOW() WHERE id=?'
        );

        $q->execute([
            $this->v('title', 180),
            $slug,
            $this->v('body', 20000),
            in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true)
                ? $_POST['status']
                : 'draft',
            $id
        ]);

        $this->logAudit('update', 'cms_page', $id);
        $this->go('/admin/management/content');
    }

    public function contentDelete(): void
    {
        $this->guard('cms.manage');
        $this->verify();

        $id = $this->id();

        $q = $this->db->prepare('DELETE FROM cms_pages WHERE id=?');
        $q->execute([$id]);

        $this->logAudit('delete', 'cms_page', $id);
        $this->go('/admin/management/content');
    }
}