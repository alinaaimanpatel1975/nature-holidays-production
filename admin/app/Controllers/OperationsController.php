<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Throwable;
use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;

final class OperationsController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    private function guard(string $permission = 'operations.view'): void
    {
        Auth::requireLogin();

        if (!Auth::can($permission)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    private function base(): string
    {
        return rtrim(
            (string)(defined('APP_BASE_PATH') ? APP_BASE_PATH : '/nature-holidays-php/public'),
            '/'
        );
    }

    private function id(): int
    {
        return max(0, (int)($_POST['id'] ?? $_GET['id'] ?? 0));
    }

    private function val(string $key, int $max = 5000): string
    {
        $value = trim((string)($_POST[$key] ?? ''));

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max)
            : substr($value, 0, $max);
    }

    private function uid(): ?int
    {
        $u = Auth::user() ?? [];
        $id = (int)($u['id'] ?? 0);

        return $id > 0 ? $id : null;
    }

    private function csrfToken(): string
    {
        return Csrf::token();
    }

    private function csrfCheck(): void
    {
        $token = (string)($_POST['_token'] ?? $_POST['csrf_token'] ?? '');

        if (!Csrf::verify($token)) {
            http_response_code(419);
            exit('CSRF validation failed. Refresh and retry.');
        }
    }

    private function go(string $path): never
    {
        header('Location: ' . $this->base() . $path);
        exit;
    }

    private function esc(mixed $v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    private function audit(string $action, string $type, int $id, array $details = []): void
    {
        try {
            $s = $this->db->prepare(
                "INSERT INTO audit_logs
                (user_id,action,entity_type,entity_id,ip_address,details,created_at)
                VALUES(?,?,?,?,?,?,NOW())"
            );

            $s->execute([
                $this->uid(),
                $action,
                $type,
                $id,
                $_SERVER['REMOTE_ADDR'] ?? null,
                json_encode($details)
            ]);
        } catch (Throwable $e) {
            error_log('Operations audit: ' . $e->getMessage());
        }
    }

    /**
     * Shared page renderer.
     * Uses the same layout as Management and provides the
     * Operations-specific navigation through the section value.
     */
    private function render(string $heading, string $body): void
    {
        $base = $this->base();

        $content = '<div class="ops">'
            . '<h1>' . $this->esc($heading) . '</h1>'
            . $body
            . '</div>';

        $this->view('operations/page', [
            'title' => $heading,
            'content' => $content,
            'base' => $base,
            'section' => 'operations',
        ]);
    }

    private function form(string $url, array $fields, string $button = 'Save'): string
{
    $h = '<form method="post" enctype="multipart/form-data" action="' . $this->base() . $url . '">'
        . '<input type="hidden" name="_token" value="' . $this->esc($this->csrfToken()) . '">';

    foreach ($fields as $f) {
        [$name, $label] = $f;
        $value = $f[2] ?? '';
        $type = $f[3] ?? 'text';

        if ($type === 'hidden') {
            $h .= '<input type="hidden" name="' . $this->esc($name)
                . '" value="' . $this->esc($value) . '">';
            continue;
        }

        $h .= '<label>' . $this->esc($label) . '</label>';

        if ($type === 'textarea') {
            $h .= '<textarea name="' . $this->esc($name) . '" rows="3">'
                . $this->esc($value) . '</textarea>';

        } elseif ($type === 'select') {
            $h .= '<select name="' . $this->esc($name) . '">';

            foreach (($f[4] ?? []) as $k => $v) {
                $selected = (string)$k === (string)$value ? ' selected' : '';

                $h .= '<option value="' . $this->esc($k) . '"' . $selected . '>'
                    . $this->esc($v)
                    . '</option>';
            }

            $h .= '</select>';

        } elseif ($type === 'file') {
            if ($value !== '') {
                $h .= '<div style="margin:10px 0">'
                    . '<img src="' . $this->esc($value) . '" alt="Current destination image" style="max-width:220px;max-height:140px;object-fit:cover;border-radius:8px">'
                    . '</div>';
            }

            $h .= '<input type="file" name="' . $this->esc($name)
                . '" accept="image/jpeg,image/png,image/webp">';

        } else {
            $h .= '<input type="' . $this->esc($type)
                . '" name="' . $this->esc($name)
                . '" value="' . $this->esc($value) . '">';
        }
    }

    return $h . '<div class="bar"><button type="submit">'
        . $this->esc($button)
        . '</button></div></form>';
}


private function destinationEditorForm(
    string $url,
    array $destination = [],
    array $attractions = [],
    array $activities = []
): string {
    $e = fn($value) => $this->esc((string)($value ?? ''));

    $h = '<form method="post" enctype="multipart/form-data" action="'
        . $this->base() . $url . '">'
        . '<input type="hidden" name="_token" value="'
        . $e($this->csrfToken()) . '">';

    if (!empty($destination['id'])) {
        $h .= '<input type="hidden" name="id" value="'
            . (int)$destination['id'] . '">';
    }

    $h .= '
    <style>
        .dest-editor { max-width: 1050px; }
        .dest-section {
            background:#fff; border:1px solid #e5e7eb;
            border-radius:12px; padding:22px; margin:18px 0;
        }
        .dest-section h2 { margin:0 0 16px; font-size:19px; }
        .dest-grid {
            display:grid; grid-template-columns:repeat(2,minmax(0,1fr));
            gap:15px;
        }
        .dest-field { margin-bottom:14px; }
        .dest-field label {
            display:block; font-weight:600; margin-bottom:6px;
        }
        .dest-field input, .dest-field textarea {
            width:100%; box-sizing:border-box; padding:11px;
            border:1px solid #d1d5db; border-radius:7px;
        }
        .dest-field textarea { min-height:95px; resize:vertical; }
        .dest-card {
            border:1px solid #e5e7eb; border-radius:10px;
            padding:16px; margin:14px 0; background:#fafafa;
        }
        .dest-card-head {
            display:flex; justify-content:space-between;
            align-items:center; gap:10px; margin-bottom:12px;
        }
        .dest-card-head strong { font-size:15px; }
        .dest-remove {
            border:0; background:#fee2e2; color:#991b1b;
            border-radius:6px; padding:7px 11px; cursor:pointer;
        }
        .dest-add {
            border:1px dashed #9ca3af; background:#f9fafb;
            border-radius:8px; padding:11px 16px; cursor:pointer;
            font-weight:600;
        }
        .dest-preview {
            max-width:180px; max-height:120px; object-fit:cover;
            border-radius:8px; display:block; margin:8px 0;
        }
        .dest-save {
            background:#171717; color:white; border:0;
            padding:13px 24px; border-radius:8px;
            font-weight:600; cursor:pointer;
        }
        @media(max-width:650px) {
            .dest-grid { grid-template-columns:1fr; }
            .dest-section { padding:15px; }
        }
    </style>

    <div class="dest-editor">

    <section class="dest-section">
        <h2>Destination basics</h2>
        <div class="dest-grid">';

    $basicFields = [
        ['name', 'Destination name'],
        ['slug', 'URL slug'],
        ['country', 'Country'],
        ['state', 'State'],
        ['subtitle', 'Subtitle'],
        ['source_name', 'Official tourism source name'],
        ['source_url', 'Official tourism source link']
    ];

    foreach ($basicFields as [$name, $label]) {
        $value = $destination[$name] ?? ($name === 'country' ? 'India' : '');
        $h .= '<div class="dest-field"><label>' . $e($label)
            . '</label><input type="text" name="' . $e($name)
            . '" value="' . $e($value) . '"></div>';
    }

    $h .= '</div>';

    $h .= '<div class="dest-field"><label>Short description</label>
        <textarea name="description">' . $e($destination['description'] ?? '')
        . '</textarea></div>';

    $h .= '<div class="dest-field"><label>Featured destination</label>
        <select name="is_featured">
        <option value="0"' . ((int)($destination['is_featured'] ?? 0) === 0 ? ' selected' : '') . '>No</option>
        <option value="1"' . ((int)($destination['is_featured'] ?? 0) === 1 ? ' selected' : '') . '>Yes</option>
        </select></div>';

    $h .= '<div class="dest-field"><label>Destination hero image</label>';

    if (!empty($destination['image'])) {
        $h .= '<img class="dest-preview" src="' . $e($destination['image'])
            . '" alt="Current destination image">';
    }

    $h .= '<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
    </section>

    <section class="dest-section">
        <h2>About this destination</h2>
        <div class="dest-field"><label>About</label>
        <textarea name="about" rows="6">'
        . $e($destination['about'] ?? '') . '</textarea></div>
    </section>

    <section class="dest-section">
        <h2>Famous places & experiences</h2>
        <p>Add individual places, landmarks or experiences. Each can have its own photo and links.</p>
        <div id="attraction-list">';

    $attractionCard = function (int $i, array $item = []) use ($e): string {
        $html = '<div class="dest-card attraction-card">
            <div class="dest-card-head">
                <strong>Place / experience</strong>
                <button type="button" class="dest-remove">Remove</button>
            </div>';

        $fields = [
            ['name', 'Place name'],
            ['slug', 'URL slug'],
            ['description', 'Description'],
            ['image_alt', 'Image description (alt text)'],
            ['photo_author', 'Photo credit name'],
            ['credit_url', 'Photo credit link'],
            ['map_url', 'Google Maps link']
        ];

        foreach ($fields as [$name, $label]) {
            $value = $item[$name] ?? '';

            if ($name === 'description') {
                $html .= '<div class="dest-field"><label>' . $e($label)
                    . '</label><textarea name="attractions[' . $i . '][' . $name
                    . ']">' . $e($value) . '</textarea></div>';
            } else {
                $html .= '<div class="dest-field"><label>' . $e($label)
                    . '</label><input type="text" name="attractions[' . $i
                    . '][' . $name . ']" value="' . $e($value) . '"></div>';
            }
        }

        $html .= '<div class="dest-field"><label>Place image</label>';

        if (!empty($item['image'])) {
            $html .= '<img class="dest-preview" src="' . $e($item['image'])
                . '" alt="Current place image">';
        }

        $html .= '<input type="hidden" name="attractions[' . $i
            . '][existing_image]" value="' . $e($item['image'] ?? '') . '">
            <input type="file" name="attraction_images[' . $i
            . ']" accept="image/jpeg,image/png,image/webp"></div>
            </div>';

        return $html;
    };

    foreach ($attractions as $i => $item) {
        $h .= $attractionCard((int)$i, $item);
    }

    $h .= '</div>
        <button type="button" class="dest-add" id="add-attraction">+ Add another place</button>
    </section>

    <section class="dest-section">
        <h2>Things to do</h2>
        <p>Add activities visitors can enjoy at this destination.</p>
        <div id="activity-list">';

    $activityCard = function (int $i, array $item = []) use ($e): string {
        return '<div class="dest-card activity-card">
            <div class="dest-card-head">
                <strong>Activity</strong>
                <button type="button" class="dest-remove">Remove</button>
            </div>
            <div class="dest-field"><label>Activity title</label>
            <input type="text" name="activities[' . $i . '][title]"
            value="' . $e($item['title'] ?? '') . '"></div>
            <div class="dest-field"><label>Description</label>
            <textarea name="activities[' . $i . '][description]">'
            . $e($item['description'] ?? '') . '</textarea></div>
            </div>';
    };

    foreach ($activities as $i => $item) {
        $h .= $activityCard((int)$i, $item);
    }

    $h .= '</div>
        <button type="button" class="dest-add" id="add-activity">+ Add another activity</button>
    </section>

    <section class="dest-section">
        <h2>Before you go</h2>
        <div class="dest-field"><label>Planning information</label>
        <textarea name="planning" rows="6">'
        . $e($destination['planning'] ?? '') . '</textarea></div>
    </section>

    <button type="submit" class="dest-save">Save destination</button>
    </div>

    <template id="attraction-template">'
        . $attractionCard(999999) .
    '</template>

    <template id="activity-template">'
        . $activityCard(999999) .
    '</template>

    <script>
    (() => {
        let attractionIndex = ' . count($attractions) . ';
        let activityIndex = ' . count($activities) . ';

        function addCard(templateId, listId, index) {
            const template = document.getElementById(templateId);
            const list = document.getElementById(listId);
            const html = template.innerHTML.replaceAll("999999", index);
            list.insertAdjacentHTML("beforeend", html);
        }

        document.getElementById("add-attraction").addEventListener("click", () => {
            addCard("attraction-template", "attraction-list", attractionIndex++);
        });

        document.getElementById("add-activity").addEventListener("click", () => {
            addCard("activity-template", "activity-list", activityIndex++);
        });

        document.addEventListener("click", (event) => {
            if (event.target.classList.contains("dest-remove")) {
                event.target.closest(".dest-card").remove();
            }
        });
    })();
    </script>';

    return $h . '</form>';
}


    private function table(array $heads, array $rows): string
    {
        $h = '<div class="panel"><table><thead><tr>';

        foreach ($heads as $x) {
            $h .= '<th>' . $this->esc($x) . '</th>';
        }

        $h .= '</tr></thead><tbody>';

        foreach ($rows as $r) {
            $h .= '<tr>';

            foreach ($r as $c) {
                $h .= '<td>' . $c . '</td>';
            }

            $h .= '</tr>';
        }

        return $h . '</tbody></table></div>';
    }

    private function customers(): array
    {
        return $this->db->query(
            "SELECT id,name,email FROM customers ORDER BY name"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    private function packages(): array
    {
        return $this->db->query(
            "SELECT id,name FROM packages ORDER BY name"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function dashboard(): void
    {
        $this->guard();

        $names = [
            'destinations' => 'Destinations',
            'suppliers' => 'Suppliers',
            'quotations' => 'Quotations',
            'bookings' => 'Bookings',
            'payment_transactions' => 'Payment entries'
        ];

        $body = '<div class="cards">';

        foreach ($names as $t => $label) {
            $n = (int)$this->db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

            $body .= '<div class="card">'
                . $this->esc($label)
                . '<div class="num">' . $n . '</div>'
                . '</div>';
        }

        $body .= '</div><div class="bar">';

        foreach ([
            '/admin/destinations' => 'Destinations',
            '/admin/suppliers' => 'Suppliers',
            '/admin/quotations' => 'Quotations',
            '/admin/bookings' => 'Bookings',
            '/admin/payments' => 'Payments'
        ] as $u => $l) {
            $body .= '<a class="btn" href="' . $this->base() . $u . '">' . $l . '</a>';
        }

        $this->render('Operations workspace', $body);
    }

    public function destinations(): void
    {
        $this->guard('destinations.manage');

        $rs = $this->db->query(
            "SELECT d.*,
            (SELECT COUNT(*) FROM package_destinations p WHERE p.destination_id=d.id) uses_count
            FROM destinations d ORDER BY d.name"
        )->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];

        foreach ($rs as $r) {
            $id = (int)$r['id'];

            $act = '<a href="' . $this->base() . '/admin/destinations/edit?id=' . $id . '">Edit</a> '
                . '<form class="inline" method="post" action="' . $this->base() . '/admin/destinations/toggle">'
                . '<input type="hidden" name="_token" value="' . $this->esc($this->csrfToken()) . '">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<button>Toggle</button></form> '
                . '<form class="inline" method="post" action="' . $this->base() . '/admin/destinations/delete" onsubmit="return confirm(\'Delete destination?\')">'
                . '<input type="hidden" name="_token" value="' . $this->esc($this->csrfToken()) . '">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<button class="danger">Delete</button></form>';

            $rows[] = [
                '<b>' . $this->esc($r['name']) . '</b><br>'
                    . $this->esc($r['state'] ?? '') . ', '
                    . $this->esc($r['country']),
                $r['uses_count'],
                $r['is_active'] ? 'Active' : 'Inactive',
                $act
            ];
        }

        $this->render(
            'Destinations',
            '<div class="bar"><a class="btn" href="' . $this->base()
            . '/admin/destinations/create">Add destination</a></div>'
            . $this->table(['Destination', 'Packages linked', 'Status', 'Actions'], $rows)
        );
    }

    private function uploadDestinationImage(string $current = ''): string
{
    if (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return $current;
    }

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        exit('Image upload failed. Please try again.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        exit('Image must be smaller than 5 MB.');
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        exit('Only JPG, PNG and WebP images are allowed.');
    }

    $directory = dirname(__DIR__, 2) . '/public/uploads/destinations';

    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        exit('Could not save the uploaded image.');
    }

    // Remove the previous uploaded image when replacing it.
    if (str_starts_with($current, '/uploads/destinations/')) {
        $oldFile = dirname(__DIR__, 2) . '/public' . $current;

        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    return '/uploads/destinations/' . $filename;
}

   
public function destinationCreate(): void
{
    $this->guard('destinations.manage');

    $this->render(
        'Add destination',
        $this->destinationEditorForm(
            '/admin/destinations/store',
            [],
            [],
            []
        )
    );
}

public function destinationStore(): void
{
    $this->guard('destinations.manage');
    $this->csrfCheck();

    $attractions = is_array($_POST['attractions'] ?? null)
        ? $_POST['attractions'] : [];

    $activities = is_array($_POST['activities'] ?? null)
        ? $_POST['activities'] : [];

    $image = $this->uploadDestinationImage();

    $this->db->beginTransaction();

    try {
        $q = $this->db->prepare(
            "INSERT INTO destinations
            (name,slug,country,state,description,subtitle,about,planning,
             source_url,source_name,image,is_featured,is_active,created_at,updated_at)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,1,NOW(),NOW())"
        );

        $q->execute([
            $this->val('name', 150),
            $this->val('slug', 180),
            $this->val('country', 100) ?: 'India',
            $this->val('state', 100),
            $this->val('description'),
            $this->val('subtitle', 255),
            $this->val('about'),
            $this->val('planning'),
            $this->val('source_url', 500),
            $this->val('source_name', 150),
            $image,
            (int)($_POST['is_featured'] ?? 0)
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->saveDestinationDetails(
            $id,
            $attractions,
            $activities
        );

        $this->db->commit();

    } catch (Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }

    $this->audit('create', 'destination', $id);
    $this->go('/admin/destinations');
}

public function destinationEdit(): void
{
    $this->guard('destinations.manage');

    $q = $this->db->prepare(
        "SELECT * FROM destinations WHERE id=?"
    );
    $q->execute([(int)($_GET['id'] ?? 0)]);
    $r = $q->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        exit('Not found');
    }

    $attractionsQuery = $this->db->prepare(
        "SELECT * FROM destination_attractions
         WHERE destination_id=?
         ORDER BY sort_order, id"
    );
    $attractionsQuery->execute([$r['id']]);
    $attractions = $attractionsQuery->fetchAll(PDO::FETCH_ASSOC);

    $activitiesQuery = $this->db->prepare(
        "SELECT * FROM destination_activities
         WHERE destination_id=?
         ORDER BY sort_order, id"
    );
    $activitiesQuery->execute([$r['id']]);
    $activities = $activitiesQuery->fetchAll(PDO::FETCH_ASSOC);

    $this->render(
        'Edit destination',
        $this->destinationEditorForm(
            '/admin/destinations/update',
            $r,
            $attractions,
            $activities
        )
    );
}

public function destinationUpdate(): void
{
    $this->guard('destinations.manage');
    $this->csrfCheck();

    $id = $this->id();

    $attractions = is_array($_POST['attractions'] ?? null)
        ? $_POST['attractions'] : [];

    $activities = is_array($_POST['activities'] ?? null)
        ? $_POST['activities'] : [];

    $existing = $this->db->prepare(
        "SELECT image FROM destinations WHERE id=?"
    );
    $existing->execute([$id]);

    $currentImage = (string)($existing->fetchColumn() ?: '');

    $image = $this->uploadDestinationImage($currentImage);

    $this->db->beginTransaction();

    try {
        $q = $this->db->prepare(
            "UPDATE destinations SET
            name=?, slug=?, country=?, state=?, description=?,
            subtitle=?, about=?, planning=?, source_url=?, source_name=?,
            image=?, is_featured=?, updated_at=NOW()
            WHERE id=?"
        );

        $q->execute([
            $this->val('name', 150),
            $this->val('slug', 180),
            $this->val('country', 100) ?: 'India',
            $this->val('state', 100),
            $this->val('description'),
            $this->val('subtitle', 255),
            $this->val('about'),
            $this->val('planning'),
            $this->val('source_url', 500),
            $this->val('source_name', 150),
            $image,
            (int)($_POST['is_featured'] ?? 0),
            $id
        ]);

        $this->saveDestinationDetails(
            $id,
            $attractions,
            $activities
        );

$this->db->commit();

} catch (Throwable $e) {
    $this->db->rollBack();
    throw $e;
}

$this->audit('update', 'destination', $id);
$this->go('/admin/destinations');
}


private function saveDestinationDetails(
    int $destinationId,
    array $attractions,
    array $activities
): void {
    $deleteAttractions = $this->db->prepare(
        "DELETE FROM destination_attractions WHERE destination_id=?"
    );
    $deleteAttractions->execute([$destinationId]);

    $deleteActivities = $this->db->prepare(
        "DELETE FROM destination_activities WHERE destination_id=?"
    );
    $deleteActivities->execute([$destinationId]);

    $insertAttraction = $this->db->prepare(
        "INSERT INTO destination_attractions
        (destination_id,name,slug,description,image,image_alt,
         photo_author,credit_url,map_url,sort_order,is_active)
        VALUES(?,?,?,?,?,?,?,?,?,?,1)"
    );

    $usedSlugs = [];
    $sortOrder = 0;

    foreach ($attractions as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $name = trim((string)($item['name'] ?? ''));

        if ($name === '') {
            continue;
        }

        $slug = trim((string)($item['slug'] ?? ''));

        if ($slug === '') {
            $slug = strtolower($name);
        }

        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim(strtolower($slug), '-');

        if ($slug === '') {
            $slug = 'place-' . ($sortOrder + 1);
        }

        $baseSlug = $slug;
        $suffix = 2;

        while (in_array($slug, $usedSlugs, true)) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $usedSlugs[] = $slug;

        $image = $this->uploadDestinationAttractionImage((string)$index);

        if ($image === '') {
            $existingImage = (string)($item['existing_image'] ?? '');

            if (
                str_starts_with($existingImage, '/uploads/destinations/')
                && !str_contains($existingImage, '..')
            ) {
                $image = $existingImage;
            }
        }

        $insertAttraction->execute([
            $destinationId,
            $name,
            $slug,
            trim((string)($item['description'] ?? '')),
            $image,
            trim((string)($item['image_alt'] ?? '')),
            trim((string)($item['photo_author'] ?? '')),
            trim((string)($item['credit_url'] ?? '')),
            trim((string)($item['map_url'] ?? '')),
            $sortOrder++
        ]);
    }

    $insertActivity = $this->db->prepare(
        "INSERT INTO destination_activities
        (destination_id,title,description,sort_order,is_active)
        VALUES(?,?,?,?,1)"
    );

    $sortOrder = 0;

    foreach ($activities as $item) {
        if (!is_array($item)) {
            continue;
        }

        $title = trim((string)($item['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $insertActivity->execute([
            $destinationId,
            $title,
            trim((string)($item['description'] ?? '')),
            $sortOrder++
        ]);
    }
}

private function uploadDestinationAttractionImage(string $index): string
{
    if (
        !isset($_FILES['attraction_images'])
        || !is_array($_FILES['attraction_images']['name'] ?? null)
    ) {
        return '';
    }

    $file = $_FILES['attraction_images'];

    $error = $file['error'][$index] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('A place image could not be uploaded.');
    }

    $tmpName = $file['tmp_name'][$index] ?? '';
    $size = (int)($file['size'][$index] ?? 0);

    if (!$tmpName || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Invalid place image upload.');
    }

    if ($size > 5 * 1024 * 1024) {
        throw new RuntimeException('Place images must be 5 MB or smaller.');
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($extensions[$mime])) {
        throw new RuntimeException(
            'Place images must be JPG, PNG or WebP.'
        );
    }

    $directory = dirname(__DIR__, 2)
        . '/public/uploads/destinations';

    if (
        !is_dir($directory)
        && !mkdir($directory, 0755, true)
        && !is_dir($directory)
    ) {
        throw new RuntimeException('Could not create the upload folder.');
    }

    $filename = bin2hex(random_bytes(16))
        . '.' . $extensions[$mime];

    if (!move_uploaded_file(
        $tmpName,
        $directory . '/' . $filename
    )) {
        throw new RuntimeException('Could not save the place image.');
    }

    return '/uploads/destinations/' . $filename;
}



    public function destinationToggle(): void
    {
        $this->guard('destinations.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "UPDATE destinations SET is_active=1-is_active WHERE id=?"
        );

        $q->execute([$this->id()]);
        $this->audit('toggle', 'destination', $this->id());
        $this->go('/admin/destinations');
    }

    public function destinationDelete(): void
    {
        $this->guard('destinations.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "SELECT COUNT(*) FROM package_destinations WHERE destination_id=?"
        );

        $q->execute([$this->id()]);

        if ((int)$q->fetchColumn() > 0) {
            $_SESSION['ops_flash'] = 'Destination is linked to a package. Deactivate it instead.';
            $this->go('/admin/destinations');
        }

        $q = $this->db->prepare("DELETE FROM destinations WHERE id=?");
        $q->execute([$this->id()]);

        $this->audit('delete', 'destination', $this->id());
        $this->go('/admin/destinations');
    }

    public function suppliers(): void
    {
        $this->guard('suppliers.manage');

        $rs = $this->db->query("SELECT * FROM suppliers ORDER BY name")
            ->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];

        foreach ($rs as $r) {
            $rows[] = [
                '<b>' . $this->esc($r['name']) . '</b><br>' . $this->esc($r['supplier_type']),
                $this->esc($r['contact_name']) . '<br>' . $this->esc($r['phone']),
                $this->esc($r['city']) . ', ' . $this->esc($r['state']),
                $this->esc($r['status']),
                '<a href="' . $this->base() . '/admin/suppliers/edit?id=' . $r['id'] . '">Edit</a> '
                . '<form class="inline" method="post" action="' . $this->base() . '/admin/suppliers/toggle">'
                . '<input type="hidden" name="_token" value="' . $this->esc($this->csrfToken()) . '">'
                . '<input type="hidden" name="id" value="' . $r['id'] . '">'
                . '<button>Toggle</button></form>'
            ];
        }

        $this->render(
            'Suppliers',
            '<div class="bar"><a class="btn" href="' . $this->base()
            . '/admin/suppliers/create">Add supplier</a></div>'
            . $this->table(['Supplier', 'Contact', 'Location', 'Status', 'Actions'], $rows)
        );
    }

    private function supplierFields(array $r = []): array
    {
        return [
            ['id', '', (int)($r['id'] ?? 0), 'hidden'],
            ['name', 'Supplier name', $r['name'] ?? ''],
            ['supplier_type', 'Type', $r['supplier_type'] ?? 'other', 'select', [
                'hotel' => 'Hotel',
                'transport' => 'Transport',
                'guide' => 'Guide',
                'activity' => 'Activity',
                'other' => 'Other'
            ]],
            ['contact_name', 'Contact person', $r['contact_name'] ?? ''],
            ['email', 'Email', $r['email'] ?? '', 'email'],
            ['phone', 'Phone', $r['phone'] ?? ''],
            ['city', 'City', $r['city'] ?? ''],
            ['state', 'State', $r['state'] ?? ''],
            ['services', 'Services', $r['services'] ?? '', 'textarea'],
            ['notes', 'Notes', $r['notes'] ?? '', 'textarea']
        ];
    }

    public function supplierCreate(): void
    {
        $this->guard('suppliers.manage');
        $this->render('Add supplier', $this->form('/admin/suppliers/store', $this->supplierFields()));
    }

    public function supplierStore(): void
    {
        $this->guard('suppliers.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "INSERT INTO suppliers(name,supplier_type,contact_name,email,phone,city,state,services,notes)
            VALUES(?,?,?,?,?,?,?,?,?)"
        );

        $q->execute([
            $this->val('name', 160),
            $this->val('supplier_type', 60),
            $this->val('contact_name', 120),
            $this->val('email', 190),
            $this->val('phone', 40),
            $this->val('city', 100),
            $this->val('state', 100),
            $this->val('services'),
            $this->val('notes')
        ]);

        $id = (int)$this->db->lastInsertId();
        $this->audit('create', 'supplier', $id);
        $this->go('/admin/suppliers');
    }

    public function supplierEdit(): void
    {
        $this->guard('suppliers.manage');

        $q = $this->db->prepare("SELECT * FROM suppliers WHERE id=?");
        $q->execute([(int)($_GET['id'] ?? 0)]);
        $r = $q->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            exit('Not found');
        }

        $this->render('Edit supplier', $this->form('/admin/suppliers/update', $this->supplierFields($r)));
    }

    public function supplierUpdate(): void
    {
        $this->guard('suppliers.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "UPDATE suppliers SET name=?,supplier_type=?,contact_name=?,email=?,phone=?,city=?,state=?,services=?,notes=? WHERE id=?"
        );

        $q->execute([
            $this->val('name', 160),
            $this->val('supplier_type', 60),
            $this->val('contact_name', 120),
            $this->val('email', 190),
            $this->val('phone', 40),
            $this->val('city', 100),
            $this->val('state', 100),
            $this->val('services'),
            $this->val('notes'),
            $this->id()
        ]);

        $this->audit('update', 'supplier', $this->id());
        $this->go('/admin/suppliers');
    }

    public function supplierToggle(): void
    {
        $this->guard('suppliers.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "UPDATE suppliers SET status=IF(status='active','inactive','active') WHERE id=?"
        );

        $q->execute([$this->id()]);
        $this->audit('toggle', 'supplier', $this->id());
        $this->go('/admin/suppliers');
    }

    
    /*
    |--------------------------------------------------------------------------
    | QUOTATIONS
    |--------------------------------------------------------------------------
    */

    
/* =========================================================
   QUOTATIONS
   ========================================================= */

public function quotations(): void
{
    $this->guard('quotations.manage');

    $rs = $this->db->query(
        "SELECT q.*, c.name AS customer
         FROM quotations q
         LEFT JOIN customers c ON c.id = q.customer_id
         ORDER BY q.id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $rows = [];

    foreach ($rs as $r) {
        $id = (int)$r['id'];
        $currency = $this->esc($r['currency']);

        $rows[] = [
            '<a href="' . $this->base()
                . '/admin/quotations/view?id=' . $id . '">'
                . $this->esc($r['quotation_number']) . '</a>',
            $this->esc($r['title']),
            $this->esc($r['customer'] ?? '—'),
            $currency . ' ' . number_format((float)$r['total_amount'], 2),
            $this->esc(ucfirst($r['status'])),
            $this->esc($r['valid_until'] ?? '—'),
            '<a href="' . $this->base()
                . '/admin/quotations/view?id=' . $id . '">View</a> '
                . '<a href="' . $this->base()
                . '/admin/quotations/edit?id=' . $id . '">Edit</a>'
        ];
    }

    $this->render(
        'Quotations',
        '<div class="bar"><a class="btn" href="' . $this->base()
        . '/admin/quotations/create">Create quotation</a></div>'
        . $this->table(
            ['Reference', 'Title', 'Customer', 'Total', 'Status', 'Valid until', 'Actions'],
            $rows
        )
    );
}

/**
 * Builds the quotation create/edit form.
 */
private function quotationFormHtml(
    array $r,
    array $items,
    string $action,
    array $customers,
    array $packages,
    bool $editing
): string {
    $e = fn($value) => $this->esc($value);

    $customerOptions = '<option value="">Select customer</option>';

    foreach ($customers as $c) {
        $selected = (string)($r['customer_id'] ?? '') === (string)$c['id']
            ? ' selected'
            : '';

        $customerOptions .= '<option value="' . (int)$c['id'] . '"'
            . $selected . '>' . $e($c['name'] . ' — ' . $c['email'])
            . '</option>';
    }

    $packageOptions = '<option value="">No package</option>';

    foreach ($packages as $p) {
        $selected = (string)($r['package_id'] ?? '') === (string)$p['id']
            ? ' selected'
            : '';

        $packageOptions .= '<option value="' . (int)$p['id'] . '"'
            . $selected . '>' . $e($p['name']) . '</option>';
    }

    $field = function (
    string $name,
    string $label,
    string $type = 'text',
    $value = '',
    string $extra = ''
) use ($e): string {
        return '<div class="q-field">'
            . '<label for="' . $e($name) . '">' . $e($label) . '</label>'
            . '<input id="' . $e($name) . '" name="' . $e($name)
            . '" type="' . $e($type) . '" value="' . $e($value)
            . '" ' . $extra . '>'
            . '</div>';
    };

    $textarea = function (
        string $name,
        string $label,
        string $value = ''
    ) use ($e): string {
        return '<div class="q-field q-full">'
            . '<label for="' . $e($name) . '">' . $e($label) . '</label>'
            . '<textarea id="' . $e($name) . '" name="' . $e($name)
            . '" rows="4">' . $e($value) . '</textarea>'
            . '</div>';
    };

    if (!$items) {
        $items = [[
            'id' => 0,
            'description' => '',
            'quantity' => '1',
            'unit_price' => '0'
        ]];
    }

    $itemRows = '';

    foreach ($items as $item) {
        $itemRows .= '<div class="q-item-row">'
            . '<input type="hidden" name="item_id[]" value="'
            . (int)($item['id'] ?? 0) . '">'
            . '<div class="q-field q-description">'
            . '<label>Item description</label>'
            . '<input name="item_description[]" maxlength="255" required value="'
            . $e($item['description'] ?? '') . '">'
            . '</div>'
            . '<div class="q-field">'
            . '<label>Quantity</label>'
            . '<input class="q-quantity" type="number" name="quantity[]"'
            . ' min="0.01" step="0.01" required value="'
            . $e($item['quantity'] ?? '1') . '">'
            . '</div>'
            . '<div class="q-field">'
            . '<label>Unit price</label>'
            . '<input class="q-price" type="number" name="unit_price[]"'
            . ' min="0" step="0.01" required value="'
            . $e($item['unit_price'] ?? '0') . '">'
            . '</div>'
            . '<div class="q-field q-line-total">'
            . '<label>Line total</label>'
            . '<output>0.00</output>'
            . '</div>'
            . '<button type="button" class="q-remove" aria-label="Remove item">×</button>'
            . '</div>';
    }

    $html = <<<'HTML'
<style>
.q-form { margin-top:20px; }
.q-section {
    background:#fff;
    border:1px solid #e3e9e2;
    border-radius:14px;
    padding:20px;
    margin:18px 0;
}
.q-section h2 { margin:0 0 5px; }
.q-section > p { color:#718076; font-size:13px; margin:0 0 14px; }
.q-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
}
.q-field { min-width:0; }
.q-field label {
    display:block;
    font-size:13px;
    font-weight:700;
    margin:0 0 6px;
}
.q-field input,.q-field select,.q-field textarea {
    width:100%;
    border:1px solid #d7e0d6;
    border-radius:8px;
    padding:11px 12px;
    font:inherit;
    background:#fff;
    color:#20372b;
}
.q-field textarea { min-height:95px; resize:vertical; }
.q-full { grid-column:1/-1; }
.q-item-row {
    display:grid;
    grid-template-columns:minmax(180px,2fr) minmax(90px,.7fr)
        minmax(110px,.9fr) minmax(100px,.8fr) 36px;
    align-items:end;
    gap:10px;
    padding:13px 0;
    border-bottom:1px solid #e3e9e2;
}
.q-item-row:first-child { padding-top:0; }
.q-item-row .q-field label { font-size:12px; }
.q-line-total output {
    display:block;
    padding:11px 0;
    font-weight:700;
}
.q-remove {
    background:#fff!important;
    color:#a33d32!important;
    border:1px solid #f0d5d1!important;
    font-size:20px!important;
    padding:6px!important;
    height:42px;
}
.q-add { margin-top:14px; }
.q-totals {
    max-width:380px;
    margin:18px 0 0 auto;
    padding:16px;
    border-radius:10px;
    background:#f5f7f3;
}
.q-total-line {
    display:flex;
    justify-content:space-between;
    gap:15px;
    margin:7px 0;
}
.q-grand {
    border-top:1px solid #d7e0d6;
    padding-top:12px;
    margin-top:12px;
    font-size:18px;
    font-weight:800;
}
.q-help { color:#718076; font-size:12px; }
@media(max-width:760px) {
    .q-grid { grid-template-columns:1fr; }
    .q-full { grid-column:auto; }
    .q-item-row {
        grid-template-columns:1fr 1fr;
        border:1px solid #e3e9e2;
        border-radius:10px;
        padding:12px;
        margin:12px 0;
    }
    .q-description { grid-column:1/-1; }
    .q-remove { grid-column:2; justify-self:end; width:42px; }
}
</style>
HTML;

    $html .= '<form class="q-form" method="post" action="'
        . $this->base() . $e($action) . '">';

    $html .= '<input type="hidden" name="_token" value="'
        . $e($this->csrfToken()) . '">';

    if ($editing) {
        $html .= '<input type="hidden" name="id" value="'
            . (int)$r['id'] . '">';
    }

    $html .= '<section class="q-section">'
        . '<h2>Quotation details</h2>'
        . '<p>Basic information and customer details.</p>'
        . '<div class="q-grid">';

    $html .= $field(
        'title',
        'Quotation title',
        'text',
        $r['title'] ?? '',
        'maxlength="190" required'
    );

    $html .= '<div class="q-field"><label for="customer_id">Customer</label>'
        . '<select id="customer_id" name="customer_id">'
        . $customerOptions . '</select></div>';

    $html .= '<div class="q-field"><label for="package_id">Package</label>'
        . '<select id="package_id" name="package_id">'
        . $packageOptions . '</select></div>';

    $html .= $field(
        'currency',
        'Currency',
        'text',
        $r['currency'] ?? 'INR',
        'maxlength="3" pattern="[A-Za-z]{3}" required'
    );

    $html .= $field(
        'valid_until',
        'Quotation valid until',
        'date',
        $r['valid_until'] ?? ''
    );

    $html .= '</div></section>';

    $html .= '<section class="q-section">'
        . '<h2>Trip details</h2>'
        . '<p>Travel information for this quotation.</p>'
        . '<div class="q-grid">';

    $html .= $field(
        'destination',
        'Destination',
        'text',
        $r['destination'] ?? '',
        'maxlength="190"'
    );

    $html .= $field(
        'travel_start_date',
        'Travel start date',
        'date',
        $r['travel_start_date'] ?? ''
    );

    $html .= $field(
        'travel_end_date',
        'Travel end date',
        'date',
        $r['travel_end_date'] ?? ''
    );

    $html .= $field(
        'traveller_count',
        'Number of travellers',
        'number',
        $r['traveller_count'] ?? '',
        'min="1" step="1"'
    );

    $html .= '</div></section>';

    $html .= '<section class="q-section">'
        . '<h2>Accommodation</h2>'
        . '<p>Hotel and room information, if applicable.</p>'
        . '<div class="q-grid">';

    $html .= $field(
        'accommodation_name',
        'Accommodation name',
        'text',
        $r['accommodation_name'] ?? '',
        'maxlength="190"'
    );

    $html .= $textarea(
        'room_details',
        'Room details',
        $r['room_details'] ?? ''
    );

    $html .= '</div></section>';

    $html .= '<section class="q-section">'
        . '<h2>Itemised pricing</h2>'
        . '<p>Add as many cost components as this quotation needs.</p>'
        . '<div id="q-items">' . $itemRows . '</div>'
        . '<button type="button" class="q-add" id="q-add-item">+ Add pricing row</button>'
        . '<div class="q-grid" style="margin-top:20px">'
        . $field(
            'discount_amount',
            'Discount amount',
            'number',
            $r['discount_amount'] ?? '0',
            'min="0" step="0.01"'
        )
        . $field(
            'tax_amount',
            'Tax amount',
            'number',
            $r['tax_amount'] ?? '0',
            'min="0" step="0.01"'
        )
        . '</div>'
        . '<div class="q-totals">'
        . '<div class="q-total-line"><span>Subtotal</span><strong id="q-subtotal">0.00</strong></div>'
        . '<div class="q-total-line"><span>Discount</span><strong id="q-discount">0.00</strong></div>'
        . '<div class="q-total-line"><span>Tax</span><strong id="q-tax">0.00</strong></div>'
        . '<div class="q-total-line q-grand"><span>Total</span><strong id="q-total">0.00</strong></div>'
        . '</div>'
        . '<p class="q-help">Totals are recalculated and validated by the server when saved.</p>'
        . '</section>';

    $html .= '<section class="q-section">'
        . '<h2>What\'s included?</h2>'
        . '<p>Make the scope of the trip clear.</p>'
        . '<div class="q-grid">'
        . $textarea('inclusions', 'Inclusions', $r['inclusions'] ?? '')
        . $textarea('exclusions', 'Exclusions', $r['exclusions'] ?? '')
        . '</div></section>';

    $html .= '<section class="q-section">'
        . '<h2>Terms and conditions</h2>'
        . '<div class="q-grid">'
        . $textarea('payment_terms', 'Payment terms', $r['payment_terms'] ?? '')
        . $textarea('cancellation_policy', 'Cancellation policy', $r['cancellation_policy'] ?? '')
        . $textarea('notes', 'Internal or additional notes', $r['notes'] ?? '')
        . '</div></section>';

    $html .= '<div class="bar">'
        . '<button class="btn" type="submit">'
        . ($editing ? 'Save quotation changes' : 'Create quotation')
        . '</button>'
        . '<a class="btn secondary" href="' . $this->base()
        . '/admin/quotations">Cancel</a>'
        . '</div></form>';

    $html .= <<<'HTML'
<template id="q-item-template">
<div class="q-item-row">
    <input type="hidden" name="item_id[]" value="0">
    <div class="q-field q-description">
        <label>Item description</label>
        <input name="item_description[]" maxlength="255" required>
    </div>
    <div class="q-field">
        <label>Quantity</label>
        <input class="q-quantity" type="number" name="quantity[]"
            min="0.01" step="0.01" required value="1">
    </div>
    <div class="q-field">
        <label>Unit price</label>
        <input class="q-price" type="number" name="unit_price[]"
            min="0" step="0.01" required value="0">
    </div>
    <div class="q-field q-line-total">
        <label>Line total</label>
        <output>0.00</output>
    </div>
    <button type="button" class="q-remove" aria-label="Remove item">×</button>
</div>
</template>

<script>
(function () {
    const root = document.querySelector('.q-form');
    if (!root) return;

    const list = root.querySelector('#q-items');
    const template = document.querySelector('#q-item-template');
    const addButton = root.querySelector('#q-add-item');
    const discountInput = root.querySelector('[name="discount_amount"]');
    const taxInput = root.querySelector('[name="tax_amount"]');

    const money = value => Number(value || 0).toFixed(2);

    function calculate() {
        let subtotal = 0;

        list.querySelectorAll('.q-item-row').forEach(row => {
            const quantity = Number(row.querySelector('.q-quantity').value || 0);
            const price = Number(row.querySelector('.q-price').value || 0);
            const line = quantity * price;

            row.querySelector('output').textContent = money(line);
            subtotal += line;
        });

        const discount = Number(discountInput.value || 0);
        const tax = Number(taxInput.value || 0);
        const total = subtotal - discount + tax;

        root.querySelector('#q-subtotal').textContent = money(subtotal);
        root.querySelector('#q-discount').textContent = money(discount);
        root.querySelector('#q-tax').textContent = money(tax);
        root.querySelector('#q-total').textContent = money(total);
    }

    addButton.addEventListener('click', () => {
        list.appendChild(template.content.cloneNode(true));
        calculate();
    });

    list.addEventListener('click', event => {
        const remove = event.target.closest('.q-remove');
        if (!remove) return;

        const rows = list.querySelectorAll('.q-item-row');

        if (rows.length <= 1) {
            const row = remove.closest('.q-item-row');
            row.querySelector('[name="item_id[]"]').value = '0';
            row.querySelector('[name="item_description[]"]').value = '';
            row.querySelector('[name="quantity[]"]').value = '1';
            row.querySelector('[name="unit_price[]"]').value = '0';
        } else {
            remove.closest('.q-item-row').remove();
        }

        calculate();
    });

    root.addEventListener('input', event => {
        if (
            event.target.matches('.q-quantity') ||
            event.target.matches('.q-price') ||
            event.target === discountInput ||
            event.target === taxInput
        ) {
            calculate();
        }
    });

    calculate();
})();
</script>
HTML;

    return $html;
}

public function quotationCreate(): void
{
    $this->guard('quotations.manage');

    $customers = $this->customers();
    $packages = $this->packages();

    $this->render(
        'Create quotation',
        $this->quotationFormHtml(
            [],
            [],
            '/admin/quotations/store',
            $customers,
            $packages,
            false
        )
    );
}

public function quotationStore(): void
{
    $this->saveQuotation(false);
}

public function quotationEdit(): void
{
    $this->guard('quotations.manage');

    $q = $this->db->prepare(
        "SELECT * FROM quotations WHERE id = ?"
    );

    $q->execute([(int)($_GET['id'] ?? 0)]);
    $r = $q->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        exit('Quotation not found.');
    }

    $it = $this->db->prepare(
        "SELECT * FROM quotation_items
         WHERE quotation_id = ?
         ORDER BY id"
    );

    $it->execute([$r['id']]);
    $items = $it->fetchAll(PDO::FETCH_ASSOC);

    $this->render(
        'Edit quotation',
        '<p class="muted">Reference: '
        . $this->esc($r['quotation_number'])
        . ' · Status: ' . $this->esc($r['status']) . '</p>'
        . $this->quotationFormHtml(
            $r,
            $items,
            '/admin/quotations/update',
            $this->customers(),
            $this->packages(),
            true
        )
    );
}

public function quotationUpdate(): void
{
    $this->saveQuotation(true);
}

/**
 * Shared server-side validation and persistence for create/edit.
 */
private function saveQuotation(bool $updating): void
{
    $this->guard('quotations.manage');
    $this->csrfCheck();

    $id = $updating ? $this->id() : 0;

    if ($updating && $id <= 0) {
        exit('Invalid quotation.');
    }

    $title = $this->val('title', 190);
    $currency = strtoupper($this->val('currency', 3) ?: 'INR');

    $customerId = (int)($_POST['customer_id'] ?? 0);
    $packageId = (int)($_POST['package_id'] ?? 0);

    $validUntil = $this->val('valid_until', 10) ?: null;
    $startDate = $this->val('travel_start_date', 10) ?: null;
    $endDate = $this->val('travel_end_date', 10) ?: null;

    $destination = $this->val('destination', 190);
    $accommodation = $this->val('accommodation_name', 190);

    $roomDetails = $this->val('room_details');
    $inclusions = $this->val('inclusions');
    $exclusions = $this->val('exclusions');
    $paymentTerms = $this->val('payment_terms');
    $cancellationPolicy = $this->val('cancellation_policy');
    $notes = $this->val('notes');

    $travellerRaw = trim((string)($_POST['traveller_count'] ?? ''));
    $travellerCount = null;

    if ($travellerRaw !== '') {
        if (!ctype_digit($travellerRaw) || (int)$travellerRaw < 1) {
            exit('Traveller count must be a positive whole number.');
        }

        $travellerCount = (int)$travellerRaw;
    }

    if (
        !$title ||
        !preg_match('/^[A-Z]{3}$/', $currency)
    ) {
        exit('Enter a quotation title and a valid three-letter currency code.');
    }

    foreach ([
        'quotation expiry date' => $validUntil,
        'travel start date' => $startDate,
        'travel end date' => $endDate
    ] as $label => $dateValue) {
        if ($dateValue !== null) {
            $date = \DateTime::createFromFormat('!Y-m-d', $dateValue);
            $errors = \DateTime::getLastErrors();

            if (
                !$date ||
                $date->format('Y-m-d') !== $dateValue ||
                ($errors !== false && ($errors['warning_count'] || $errors['error_count']))
            ) {
                exit('Invalid ' . $label . '.');
            }
        }
    }

    if ($startDate && $endDate && $endDate < $startDate) {
        exit('Travel end date cannot be before the start date.');
    }

    if ($customerId > 0) {
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM customers WHERE id = ?"
        );
        $check->execute([$customerId]);

        if ((int)$check->fetchColumn() === 0) {
            exit('Selected customer does not exist.');
        }
    }

    if ($packageId > 0) {
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM packages WHERE id = ?"
        );
        $check->execute([$packageId]);

        if ((int)$check->fetchColumn() === 0) {
            exit('Selected package does not exist.');
        }
    }

    $descriptions = $_POST['item_description'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['unit_price'] ?? [];
    $itemIds = $_POST['item_id'] ?? [];

    if (
        !is_array($descriptions) ||
        !is_array($quantities) ||
        !is_array($prices) ||
        !is_array($itemIds) ||
        count($descriptions) !== count($quantities) ||
        count($descriptions) !== count($prices) ||
        count($descriptions) !== count($itemIds)
    ) {
        exit('Invalid pricing rows.');
    }

    $items = [];
    $subtotal = 0.0;

    foreach ($descriptions as $index => $rawDescription) {
        if (!is_scalar($rawDescription)) {
            exit('Invalid item description.');
        }

        $description = trim((string)$rawDescription);
        $quantityRaw = $quantities[$index];
        $priceRaw = $prices[$index];
        $itemIdRaw = $itemIds[$index];

        if (!is_scalar($quantityRaw) || !is_scalar($priceRaw) || !is_scalar($itemIdRaw)) {
            exit('Invalid pricing row values.');
        }

        $quantityText = trim((string)$quantityRaw);
        $priceText = trim((string)$priceRaw);
        $itemId = (int)$itemIdRaw;

        // Ignore a completely blank extra row.
        if (
            $description === '' &&
            ($quantityText === '' || $quantityText === '1') &&
            ($priceText === '' || $priceText === '0') &&
            $itemId === 0
        ) {
            continue;
        }

        if (
            $description === '' ||
            mb_strlen($description) > 255 ||
            !is_numeric($quantityText) ||
            !is_numeric($priceText)
        ) {
            exit('Every pricing row needs a description, quantity and unit price.');
        }

        $quantity = (float)$quantityText;
        $unitPrice = (float)$priceText;

        if (
            !is_finite($quantity) ||
            !is_finite($unitPrice) ||
            $quantity <= 0 ||
            $unitPrice < 0 ||
            $itemId < 0
        ) {
            exit('Pricing rows contain invalid quantities or prices.');
        }

        $lineTotal = round($quantity * $unitPrice, 2);
        $subtotal += $lineTotal;

        $items[] = [
            'id' => $itemId,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal
        ];
    }

    if (!$items) {
        exit('Add at least one pricing row.');
    }

    $subtotal = round($subtotal, 2);

    $discountRaw = $_POST['discount_amount'] ?? '0';
    $taxRaw = $_POST['tax_amount'] ?? '0';

    if (
        !is_scalar($discountRaw) ||
        !is_scalar($taxRaw) ||
        !is_numeric((string)$discountRaw) ||
        !is_numeric((string)$taxRaw)
    ) {
        exit('Invalid discount or tax amount.');
    }

    $discount = (float)$discountRaw;
    $tax = (float)$taxRaw;

    if (
        !is_finite($discount) ||
        !is_finite($tax) ||
        $discount < 0 ||
        $tax < 0 ||
        $discount > $subtotal
    ) {
        exit('Discount or tax amount is invalid.');
    }

    $discount = round($discount, 2);
    $tax = round($tax, 2);
    $total = round($subtotal - $discount + $tax, 2);

    $this->db->beginTransaction();

    try {
        if ($updating) {
            $check = $this->db->prepare(
                "SELECT id FROM quotations WHERE id = ?"
            );
            $check->execute([$id]);

            if (!$check->fetchColumn()) {
                throw new \RuntimeException('Quotation not found.');
            }

            $existingQuery = $this->db->prepare(
                "SELECT id FROM quotation_items WHERE quotation_id = ?"
            );
            $existingQuery->execute([$id]);
            $existingIds = array_map('intval', $existingQuery->fetchAll(PDO::FETCH_COLUMN));

            $q = $this->db->prepare(
                "UPDATE quotations SET
                    customer_id = ?,
                    package_id = ?,
                    title = ?,
                    currency = ?,
                    subtotal = ?,
                    discount_amount = ?,
                    tax_amount = ?,
                    total_amount = ?,
                    valid_until = ?,
                    destination = ?,
                    travel_start_date = ?,
                    travel_end_date = ?,
                    traveller_count = ?,
                    accommodation_name = ?,
                    room_details = ?,
                    inclusions = ?,
                    exclusions = ?,
                    payment_terms = ?,
                    cancellation_policy = ?,
                    notes = ?,
                    updated_at = NOW()
                 WHERE id = ?"
            );

            $q->execute([
                $customerId ?: null,
                $packageId ?: null,
                $title,
                $currency,
                $subtotal,
                $discount,
                $tax,
                $total,
                $validUntil,
                $destination ?: null,
                $startDate,
                $endDate,
                $travellerCount,
                $accommodation ?: null,
                $roomDetails ?: null,
                $inclusions ?: null,
                $exclusions ?: null,
                $paymentTerms ?: null,
                $cancellationPolicy ?: null,
                $notes ?: null,
                $id
            ]);

        } else {
            $reference = 'NHQ-' . date('Ymd') . '-'
                . strtoupper(bin2hex(random_bytes(3)));

            $q = $this->db->prepare(
                "INSERT INTO quotations (
                    quotation_number,
                    customer_id,
                    package_id,
                    title,
                    currency,
                    subtotal,
                    discount_amount,
                    tax_amount,
                    total_amount,
                    valid_until,
                    destination,
                    travel_start_date,
                    travel_end_date,
                    traveller_count,
                    accommodation_name,
                    room_details,
                    inclusions,
                    exclusions,
                    payment_terms,
                    cancellation_policy,
                    notes,
                    status,
                    created_by,
                    created_at,
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    'draft', ?, NOW(), NOW()
                )"
            );

            $q->execute([
                $reference,
                $customerId ?: null,
                $packageId ?: null,
                $title,
                $currency,
                $subtotal,
                $discount,
                $tax,
                $total,
                $validUntil,
                $destination ?: null,
                $startDate,
                $endDate,
                $travellerCount,
                $accommodation ?: null,
                $roomDetails ?: null,
                $inclusions ?: null,
                $exclusions ?: null,
                $paymentTerms ?: null,
                $cancellationPolicy ?: null,
                $notes ?: null,
                $this->uid()
            ]);

            $id = (int)$this->db->lastInsertId();
        }

        $insertItem = $this->db->prepare(
            "INSERT INTO quotation_items (
                quotation_id, description, quantity, unit_price, line_total
             ) VALUES (?, ?, ?, ?, ?)"
        );

        $updateItem = $this->db->prepare(
            "UPDATE quotation_items SET
                description = ?,
                quantity = ?,
                unit_price = ?,
                line_total = ?
             WHERE id = ? AND quotation_id = ?"
        );

        $keptIds = [];

        foreach ($items as $item) {
            if ($updating && $item['id'] > 0) {
                if (!in_array($item['id'], $existingIds, true)) {
                    throw new \RuntimeException('A pricing row does not belong to this quotation.');
                }

                $updateItem->execute([
                    $item['description'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['line_total'],
                    $item['id'],
                    $id
                ]);

                $keptIds[] = $item['id'];
            } else {
                $insertItem->execute([
                    $id,
                    $item['description'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['line_total']
                ]);

                if ($updating) {
                    $keptIds[] = (int)$this->db->lastInsertId();
                }
            }
        }

        if ($updating) {
            foreach ($existingIds as $existingId) {
                if (!in_array($existingId, $keptIds, true)) {
                    $deleteItem = $this->db->prepare(
                        "DELETE FROM quotation_items
                         WHERE id = ? AND quotation_id = ?"
                    );
                    $deleteItem->execute([$existingId, $id]);
                }
            }
        }

        $this->db->commit();

        $this->audit(
            $updating ? 'update' : 'create',
            'quotation',
            $id,
            [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total
            ]
        );

        $this->go('/admin/quotations/view?id=' . $id);

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        throw $e;
    }
}

public function quotationShow(): void
{
    $this->guard('quotations.manage');

    $q = $this->db->prepare(
        "SELECT q.*, c.name AS customer
         FROM quotations q
         LEFT JOIN customers c ON c.id = q.customer_id
         WHERE q.id = ?"
    );

    $q->execute([(int)($_GET['id'] ?? 0)]);
    $r = $q->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        exit('Quotation not found.');
    }

    $it = $this->db->prepare(
        "SELECT * FROM quotation_items
         WHERE quotation_id = ?
         ORDER BY id"
    );

    $it->execute([$r['id']]);
    $items = $it->fetchAll(PDO::FETCH_ASSOC);

    $currency = $this->esc($r['currency']);

    $rows = [];

    foreach ($items as $item) {
        $rows[] = [
            $this->esc($item['description']),
            number_format((float)$item['quantity'], 2),
            $currency . ' ' . number_format((float)$item['unit_price'], 2),
            $currency . ' ' . number_format((float)$item['line_total'], 2)
        ];
    }

    $detail = function (string $label, mixed $value): string {
        $value = trim((string)($value ?? ''));

        if ($value === '') {
            $value = '—';
        } else {
            $value = nl2br($this->esc($value));
        }

        return '<div class="q-preview-detail">'
            . '<strong>' . $this->esc($label) . '</strong>'
            . '<div>' . $value . '</div>'
            . '</div>';
    };

    $body = <<<'HTML'
<style>
.quotation-print-area { background:#fff; }
.q-preview-header {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    flex-wrap:wrap;
    border-bottom:2px solid #28543b;
    padding-bottom:18px;
    margin-bottom:20px;
}
.q-preview-header h2 { margin:0 0 6px; }
.q-preview-meta { color:#718076; font-size:13px; }
.q-preview-section { margin:22px 0; }
.q-preview-section h3 {
    font-size:15px;
    border-bottom:1px solid #e3e9e2;
    padding-bottom:8px;
}
.q-preview-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
}
.q-preview-detail { margin:8px 0; overflow-wrap:anywhere; }
.q-preview-detail strong {
    display:block;
    font-size:11px;
    color:#718076;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:4px;
}
.q-preview-totals {
    max-width:380px;
    margin:20px 0 0 auto;
    background:#f5f7f3;
    border-radius:10px;
    padding:16px;
}
.q-preview-total {
    display:flex;
    justify-content:space-between;
    gap:15px;
    margin:8px 0;
}
.q-preview-grand {
    border-top:1px solid #d7e0d6;
    padding-top:12px;
    font-size:19px;
    font-weight:800;
}
.q-preview-note {
    white-space:normal;
    overflow-wrap:anywhere;
}
@media(max-width:650px) {
    .q-preview-grid { grid-template-columns:1fr; }
}
@media print {
    @page { size:A4; margin:15mm; }
    body * { visibility:hidden!important; }
    .quotation-print-area,
    .quotation-print-area * { visibility:visible!important; }
    .quotation-print-area {
        position:absolute!important;
        left:0!important;
        top:0!important;
        width:100%!important;
        border:0!important;
        box-shadow:none!important;
        margin:0!important;
        padding:0!important;
    }
    .no-print { display:none!important; }
    .q-preview-totals { background:#f5f7f3!important; }
    .wrap { width:100%!important; margin:0!important; }
}


/* Additional quotation PDF improvements */
@media print {

    /* Remove horizontal scrolling and scrollbar arrows */
    .quotation-print-area,
    .quotation-print-area * {
        overflow: visible !important;
        max-width: 100% !important;
    }

    /* Compact the document to use page space better */
    @page {
        size: A4;
        margin: 10mm 12mm;
    }

    .q-preview-section {
        margin: 13px 0 !important;
    }

    .q-preview-detail {
        margin: 5px 0 !important;
    }

    .q-preview-grid {
        gap: 10px 16px !important;
    }

    .q-preview-totals {
        margin-top: 12px !important;
        padding: 12px !important;
    }

    .q-preview-total {
        margin: 5px 0 !important;
    }

    /* Remove excessive blank lines between inclusions */
    .q-preview-detail br + br {
        display: none !important;
    }

    /* Keep pricing table headings and rows clean across pages */
    .quotation-print-area table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .quotation-print-area thead {
        display: table-header-group;
    }

    .quotation-print-area tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .q-preview-header,
    .q-preview-grand {
        break-inside: avoid;
        page-break-inside: avoid;
    }


/* Keep the complete quotation totals together */
.q-preview-totals {
    break-inside: avoid !important;
    page-break-inside: avoid !important;
}

.q-preview-total {
    break-inside: avoid !important;
    page-break-inside: avoid !important;
}

/* Keep headings with their following content */
.q-preview-section h3 {
    break-after: avoid;
    page-break-after: avoid;
}

}

</style>
HTML;

    $body .= '<div class="bar no-print">'
        . '<button type="button" onclick="window.print()">Print / Save as PDF</button>'
        . '<a class="btn secondary" href="' . $this->base()
        . '/admin/quotations/edit?id=' . (int)$r['id'] . '">Edit quotation</a>'
        . '<a class="btn secondary" href="' . $this->base()
        . '/admin/quotations">Back to quotations</a>'
        . '</div>';

    $body .= '<div class="panel quotation-print-area">'
        . '<div class="q-preview-header">'
        . '<div><h2>NATURE HOLIDAYS</h2>'
        . '<div class="q-preview-meta">TRAVEL QUOTATION</div></div>'
        . '<div><strong>' . $this->esc($r['quotation_number']) . '</strong>'
        . '<div class="q-preview-meta">Status: '
        . $this->esc(ucfirst($r['status'])) . '</div></div>'
        . '</div>';

    $body .= '<h2>' . $this->esc($r['title']) . '</h2>';

    $body .= '<section class="q-preview-section"><h3>Quotation details</h3>'
        . '<div class="q-preview-grid">'
        . $detail('Customer', $r['customer'] ?? '—')
        . $detail('Currency', $r['currency'])
        . $detail('Valid until', $r['valid_until'])
        . $detail('Package', $r['package_id'] ? 'Package #' . $r['package_id'] : '—')
        . '</div></section>';

    $body .= '<section class="q-preview-section"><h3>Trip details</h3>'
        . '<div class="q-preview-grid">'
        . $detail('Destination', $r['destination'])
        . $detail('Travellers', $r['traveller_count'])
        . $detail('Travel start', $r['travel_start_date'])
        . $detail('Travel end', $r['travel_end_date'])
        . '</div></section>';

    $body .= '<section class="q-preview-section"><h3>Accommodation</h3>'
        . '<div class="q-preview-grid">'
        . $detail('Accommodation', $r['accommodation_name'])
        . $detail('Room details', $r['room_details'])
        . '</div></section>';

    $body .= '<section class="q-preview-section"><h3>Pricing breakdown</h3>'
        . $this->table(
            ['Description', 'Quantity', 'Unit price', 'Line total'],
            $rows
        )
        . '<div class="q-preview-totals">'
        . '<div class="q-preview-total"><span>Subtotal</span><strong>'
        . $currency . ' ' . number_format((float)$r['subtotal'], 2) . '</strong></div>'
        . '<div class="q-preview-total"><span>Discount</span><strong>'
        . $currency . ' ' . number_format((float)$r['discount_amount'], 2) . '</strong></div>'
        . '<div class="q-preview-total"><span>Tax</span><strong>'
        . $currency . ' ' . number_format((float)$r['tax_amount'], 2) . '</strong></div>'
        . '<div class="q-preview-total q-preview-grand"><span>Total</span><strong>'
        . $currency . ' ' . number_format((float)$r['total_amount'], 2) . '</strong></div>'
        . '</div></section>';

    $body .= '<section class="q-preview-section"><h3>Inclusions and exclusions</h3>'
        . '<div class="q-preview-grid">'
        . $detail('Inclusions', $r['inclusions'])
        . $detail('Exclusions', $r['exclusions'])
        . '</div></section>';

    $body .= '<section class="q-preview-section"><h3>Terms and conditions</h3>'
        . '<div class="q-preview-grid">'
        . $detail('Payment terms', $r['payment_terms'])
        . $detail('Cancellation policy', $r['cancellation_policy'])
        . '</div></section>';

    if (!empty($r['notes'])) {
        $body .= '<section class="q-preview-section">'
            . '<h3>Additional notes</h3>'
            . '<div class="q-preview-note">'
            . nl2br($this->esc($r['notes']))
            . '</div></section>';
    }

    $body .= '<p class="q-preview-meta">Nature Holidays · Thank you for considering us for your trip.</p>'
        . '</div>';

    if ($r['status'] === 'draft') {
        $body .= '<div class="bar no-print">'
            . '<form method="post" action="' . $this->base()
            . '/admin/quotations/delete" '
            . 'onsubmit="return confirm(\'Delete this draft quotation?\')">'
            . '<input type="hidden" name="_token" value="'
            . $this->esc($this->csrfToken()) . '">'
            . '<input type="hidden" name="id" value="' . (int)$r['id'] . '">'
            . '<button class="danger" type="submit">Delete draft quotation</button>'
            . '</form></div>';
    }

    $body .= '<div class="no-print">'
        . $this->form('/admin/quotations/status', [
            ['id', '', (int)$r['id'], 'hidden'],
            ['status', 'Status', $r['status'], 'select', [
                'draft' => 'Draft',
                'sent' => 'Sent',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected',
                'expired' => 'Expired'
            ]]
        ], 'Update status')
        . '</div>';

    $this->render('Quotation details', $body);
}


    public function quotationStatus(): void
    {
        $this->guard('quotations.manage');
        $this->csrfCheck();

        $id = $this->id();
        $status = $this->val('status', 20);

        $allowed = [
            'draft',
            'sent',
            'accepted',
            'rejected',
            'expired'
        ];

        if (!in_array($status, $allowed, true)) {
            exit('Invalid quotation status.');
        }

        $q = $this->db->prepare(
            "SELECT status FROM quotations WHERE id = ?"
        );

        $q->execute([$id]);

        if ($q->fetchColumn() === false) {
            exit('Quotation not found.');
        }

        $q = $this->db->prepare(
            "UPDATE quotations
             SET status = ?, updated_at = NOW()
             WHERE id = ?"
        );

        $q->execute([$status, $id]);

        $this->audit('status', 'quotation', $id, [
            'status' => $status
        ]);

        $this->go('/admin/quotations/view?id=' . $id);
    }

    public function quotationDelete(): void
    {
        $this->guard('quotations.manage');
        $this->csrfCheck();

        $id = $this->id();

        $q = $this->db->prepare(
            "SELECT status FROM quotations WHERE id = ?"
        );

        $q->execute([$id]);
        $status = $q->fetchColumn();

        if ($status === false) {
            exit('Quotation not found.');
        }

        if ($status !== 'draft') {
            exit('Only draft quotations can be deleted.');
        }

        $this->db->beginTransaction();

        try {
            $q = $this->db->prepare(
                "DELETE FROM quotation_items WHERE quotation_id = ?"
            );
            $q->execute([$id]);

            $q = $this->db->prepare(
                "DELETE FROM quotations WHERE id = ?"
            );
            $q->execute([$id]);

            $this->db->commit();

            $this->audit('delete', 'quotation', $id);

            $this->go('/admin/quotations');

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function bookings(): void
    {
        $this->guard('bookings.manage');

        $rs = $this->db->query(
            "SELECT b.*,c.name customer FROM bookings b
            LEFT JOIN customers c ON c.id=b.customer_id
            ORDER BY b.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        

        $rows = [];

        foreach ($rs as $r) {
            $rows[] = [
                '<a href="' . $this->base() . '/admin/bookings/view?id=' . $r['id'] . '">'
                . $this->esc($r['booking_reference']) . '</a>',
                $this->esc($r['customer'] ?? '—'),
                $this->esc($r['travel_start_date'] ?? '—'),
                (int)$r['traveller_count'],
                $this->esc($r['currency']) . ' ' . number_format((float)$r['total_amount'], 2),
                $this->esc($r['status'])
            ];
        }

        $this->render(
            'Bookings',
            '<div class="bar"><a class="btn" href="' . $this->base()
            . '/admin/bookings/create">Create booking</a></div>'
            . $this->table(['Reference', 'Customer', 'Travel date', 'Travellers', 'Total', 'Status'], $rows)
        );
    }

    public function bookingCreate(): void
    {
        $this->guard('bookings.manage');

        $cs = [];
        foreach ($this->customers() as $c) {
            $cs[$c['id']] = $c['name'] . ' — ' . $c['email'];
        }

        $ps = [];
        foreach ($this->packages() as $p) {
            $ps[$p['id']] = $p['name'];
        }

        $this->render('Create booking', $this->form('/admin/bookings/store', [
            ['customer_id', 'Customer', '', 'select', ['' => 'Select customer'] + $cs],
            ['package_id', 'Package', '', 'select', ['' => 'Select package'] + $ps],
            ['travel_start_date', 'Start date', '', 'date'],
            ['travel_end_date', 'End date', '', 'date'],
            ['traveller_count', 'Traveller count', '1', 'number'],
            ['currency', 'Currency', 'INR'],
            ['total_amount', 'Agreed total', '0', 'number'],
            ['internal_notes', 'Internal notes', '', 'textarea']
        ]));
    }

    public function bookingStore(): void
    {
        $this->guard('bookings.manage');
        $this->csrfCheck();

        $count = max(1, (int)($_POST['traveller_count'] ?? 1));
        $amount = (float)($_POST['total_amount'] ?? 0);
        $start = $this->val('travel_start_date', 10) ?: null;
        $end = $this->val('travel_end_date', 10) ?: null;

        if ($amount < 0 || ($start && $end && $end < $start)) {
            exit('Invalid booking details.');
        }

        $ref = 'NHB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $q = $this->db->prepare(
            "INSERT INTO bookings
            (booking_reference,customer_id,package_id,travel_start_date,travel_end_date,traveller_count,currency,total_amount,internal_notes,created_by)
            VALUES(?,?,?,?,?,?,?,?,?,?)"
        );

        $q->execute([
            $ref,
            (int)($_POST['customer_id'] ?? 0) ?: null,
            (int)($_POST['package_id'] ?? 0) ?: null,
            $start,
            $end,
            $count,
            strtoupper($this->val('currency', 3) ?: 'INR'),
            $amount,
            $this->val('internal_notes'),
            $this->uid()
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->audit('create', 'booking', $id, ['reference' => $ref]);
        $this->go('/admin/bookings/view?id=' . $id);
    }

    public function bookingShow(): void
    {
        $this->guard('bookings.manage');

        $q = $this->db->prepare(
            "SELECT b.*,c.name customer FROM bookings b
            LEFT JOIN customers c ON c.id=b.customer_id WHERE b.id=?"
        );

        $q->execute([(int)($_GET['id'] ?? 0)]);
        $r = $q->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            exit('Not found');
        }

        // Calculate completed payments and refunds for this booking
$paymentQuery = $this->db->prepare(
    "SELECT
        COALESCE(SUM(
            CASE
                WHEN transaction_type = 'payment'
                AND status = 'completed'
                THEN amount ELSE 0
            END
        ), 0) AS total_payments,

        COALESCE(SUM(
            CASE
                WHEN transaction_type = 'refund'
                AND status = 'completed'
                THEN amount ELSE 0
            END
        ), 0) AS total_refunds

    FROM payment_transactions
    WHERE booking_id = ? AND currency = ?"
);

$paymentQuery->execute([$r['id'], $r['currency']]);
$paymentSummary = $paymentQuery->fetch(PDO::FETCH_ASSOC);

$totalPayments = (float)$paymentSummary['total_payments'];
$totalRefunds = (float)$paymentSummary['total_refunds'];

$netPaid = $totalPayments - $totalRefunds;
$balance = (float)$r['total_amount'] - $netPaid;
if ($netPaid <= 0) {
    $financialStatus = 'Unpaid';
} elseif ($netPaid < (float)$r['total_amount']) {
    $financialStatus = 'Partially paid';
} elseif ($netPaid == (float)$r['total_amount']) {
    $financialStatus = 'Fully paid';
} else {
    $financialStatus = 'Overpaid';
}

        $t = $this->db->prepare("SELECT * FROM booking_travellers WHERE booking_id=?");
        $t->execute([$r['id']]);

        $tr = [];

        foreach ($t->fetchAll(PDO::FETCH_ASSOC) as $x) {
            $tr[] = [
                $this->esc($x['full_name']),
                $this->esc($x['age']),
                $this->esc($x['traveller_type']),
                $this->esc($x['phone']),
                $this->esc($x['email'])
            ];
        }

        $i = $this->db->prepare(
            "SELECT d.*,s.name supplier FROM booking_itinerary_days d
            LEFT JOIN suppliers s ON s.id=d.supplier_id
            WHERE d.booking_id=? ORDER BY day_number"
        );

        $i->execute([$r['id']]);
        $ir = [];

        foreach ($i->fetchAll(PDO::FETCH_ASSOC) as $x) {
            $ir[] = [
                'Day ' . $x['day_number'],
                $this->esc($x['day_date']),
                $this->esc($x['title']),
                $this->esc($x['supplier'] ?? '—'),
                $this->esc($x['description'])
            ];
        }

        $sup = [];

        foreach ($this->db->query(
            "SELECT id,name FROM suppliers WHERE status='active' ORDER BY name"
        )->fetchAll(PDO::FETCH_ASSOC) as $x) {
            $sup[$x['id']] = $x['name'];
        }

        $body = '<div class="panel"><h2>'
            . $this->esc($r['booking_reference']) . '</h2><p>'
            . $this->esc($r['customer'] ?? '—') . ' · '
            . $this->esc($r['status']) . '</p><p>'
            . $this->esc($r['travel_start_date'] ?? '—') . ' → '
            . $this->esc($r['travel_end_date'] ?? '—') . ' · '
            . $r['traveller_count'] . ' travellers</p><p>'
            . $this->esc($r['currency']) . ' '
            . number_format((float)$r['total_amount'], 2)
. '</p></div>'

. '<div class="panel">'
. '<h2>Payment summary</h2>'
. '<p><strong>Financial status: '
. $this->esc($financialStatus)
. '</strong></p>'
. '<p>Total payments: ' . $this->esc($r['currency']) . ' '
. number_format($totalPayments, 2) . '</p>'
. '<p>Total refunds: ' . $this->esc($r['currency']) . ' '
. number_format($totalRefunds, 2) . '</p>'
. '<p><strong>Net amount paid: ' . $this->esc($r['currency']) . ' '
. number_format($netPaid, 2) . '</strong></p>'
. '<p><strong>Outstanding balance: ' . $this->esc($r['currency']) . ' '
. number_format($balance, 2) . '</strong></p>'
. '</div>'

            . $this->form('/admin/bookings/status', [
                ['id', '', (int)$r['id'], 'hidden'],
                ['status', 'Booking status', $r['status'], 'select', [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'in_progress' => 'In progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled'
                ]]
            ], 'Update status')

            . '<h2>Travellers</h2>'
            . $this->table(['Name', 'Age', 'Type', 'Phone', 'Email'], $tr)

            . $this->form('/admin/bookings/travellers', [
                ['id', '', (int)$r['id'], 'hidden'],
                ['full_name', 'Full name'],
                ['age', 'Age', '', 'number'],
                ['traveller_type', 'Type', 'adult', 'select', [
                    'adult' => 'Adult',
                    'child' => 'Child',
                    'infant' => 'Infant'
                ]],
                ['phone', 'Phone'],
                ['email', 'Email', '', 'email'],
                ['identity_note', 'Identity note']
            ], 'Add traveller')

            . '<h2>Itinerary</h2>'
            . $this->table(['Day', 'Date', 'Title', 'Supplier', 'Details'], $ir)

            . $this->form('/admin/bookings/itinerary', [
                ['id', '', (int)$r['id'], 'hidden'],
                ['day_number', 'Day number', '1', 'number'],
                ['day_date', 'Date', '', 'date'],
                ['title', 'Title'],
                ['supplier_id', 'Supplier', '', 'select', ['' => 'No supplier'] + $sup],
                ['description', 'Details', '', 'textarea']
            ], 'Add / replace day');

        $this->render('Booking details', $body);
    }

    public function bookingStatus(): void
    {
        $this->guard('bookings.manage');
        $this->csrfCheck();

        $s = $this->val('status', 20);

        if (!in_array($s, ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'], true)) {
            exit('Invalid status');
        }

        $q = $this->db->prepare("UPDATE bookings SET status=? WHERE id=?");
        $q->execute([$s, $this->id()]);

        $this->audit('status', 'booking', $this->id(), ['status' => $s]);
        $this->go('/admin/bookings/view?id=' . $this->id());
    }

    public function travellerStore(): void
    {
        $this->guard('bookings.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "INSERT INTO booking_travellers
            (booking_id,full_name,age,traveller_type,phone,email,identity_note)
            VALUES(?,?,?,?,?,?,?)"
        );

        $q->execute([
            $this->id(),
            $this->val('full_name', 160),
            (int)($_POST['age'] ?? 0) ?: null,
            $this->val('traveller_type', 30),
            $this->val('phone', 40),
            $this->val('email', 190),
            $this->val('identity_note', 190)
        ]);

        $this->audit('add_traveller', 'booking', $this->id());
        $this->go('/admin/bookings/view?id=' . $this->id());
    }

    public function itineraryStore(): void
    {
        $this->guard('bookings.manage');
        $this->csrfCheck();

        $q = $this->db->prepare(
            "INSERT INTO booking_itinerary_days
            (booking_id,day_number,day_date,title,supplier_id,description)
            VALUES(?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
            day_date=VALUES(day_date),
            title=VALUES(title),
            supplier_id=VALUES(supplier_id),
            description=VALUES(description)"
        );

        $q->execute([
            $this->id(),
            max(1, (int)($_POST['day_number'] ?? 1)),
            $this->val('day_date', 10) ?: null,
            $this->val('title', 190),
            (int)($_POST['supplier_id'] ?? 0) ?: null,
            $this->val('description')
        ]);

        $this->audit('update_itinerary', 'booking', $this->id());
        $this->go('/admin/bookings/view?id=' . $this->id());
    }

    public function payments(): void
    {
        $this->guard('payments.view');

        $summaryQuery = $this->db->query(
    "SELECT
        currency,
        SUM(CASE WHEN transaction_type = 'payment' AND status = 'completed'
            THEN amount ELSE 0 END) AS received,
        SUM(CASE WHEN transaction_type = 'refund' AND status = 'completed'
            THEN amount ELSE 0 END) AS refunded,
        SUM(CASE WHEN transaction_type = 'payment' AND status = 'pending'
            THEN amount ELSE 0 END) AS pending_payments,
        SUM(CASE WHEN transaction_type = 'refund' AND status = 'pending'
            THEN amount ELSE 0 END) AS pending_refunds
    FROM payment_transactions
    GROUP BY currency"
)->fetchAll(PDO::FETCH_ASSOC);

$summaryHtml = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin:20px 0;">';

foreach ($summaryQuery as $s) {
    $currency = $this->esc($s['currency']);

    $cards = [
        'Payments received' => $s['received'],
        'Refunds issued' => $s['refunded'],
        'Pending payments' => $s['pending_payments'],
        'Pending refunds' => $s['pending_refunds']
    ];

    foreach ($cards as $label => $amount) {
        $summaryHtml .= '<div style="padding:18px;border:1px solid #e4e4e7;border-radius:12px;background:#fff;">'
            . '<p style="margin:0 0 8px;color:#71717a;">' . $label . ' (' . $currency . ')</p>'
            . '<strong style="font-size:22px;">' . number_format((float)$amount, 2) . '</strong>'
            . '</div>';
    }
}

$summaryHtml .= '</div>';


        $rs = $this->db->query(
            "SELECT p.*,b.booking_reference FROM payment_transactions p
            JOIN bookings b ON b.id=p.booking_id
            ORDER BY p.transaction_date DESC,p.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];

        foreach ($rs as $r) {
            $rows[] = [
                $this->esc($r['booking_reference']),
                $this->esc($r['transaction_type']),
                $this->esc($r['transaction_date']),
                $this->esc($r['currency']) . ' ' . number_format((float)$r['amount'], 2),
                $this->esc($r['payment_method']),
                $this->esc($r['status'])
            ];
        }

        $body = $summaryHtml . $this->table(
            ['Booking', 'Type', 'Date', 'Amount', 'Method', 'Status'],
            $rows
        );

        if (Auth::can('payments.manage')) {
            $bs = [];

            foreach ($this->db->query(
                "SELECT id,booking_reference FROM bookings ORDER BY id DESC"
            )->fetchAll(PDO::FETCH_ASSOC) as $b) {
                $bs[$b['id']] = $b['booking_reference'];
            }

            $body .= $this->form('/admin/payments/store', [
                ['booking_id', 'Booking', '', 'select', ['' => 'Select booking'] + $bs],
                ['transaction_type', 'Type', 'payment', 'select', [
                    'payment' => 'Payment',
                    'refund' => 'Refund'
                ]],
                ['amount', 'Amount', '', 'number'],
                ['currency', 'Currency', 'INR'],
                ['payment_method', 'Method', 'bank_transfer'],
                ['transaction_reference', 'Reference'],
                ['transaction_date', 'Date', date('Y-m-d'), 'date'],
                ['status', 'Status', 'completed', 'select', [
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                    'cancelled' => 'Cancelled'
                ]],
                ['notes', 'Notes', '', 'textarea']
            ], 'Record transaction');
        }

        $this->render(
            'Payment ledger',
            '<p class="muted">Manual records only — no money is collected or transferred here.</p>'
            . $body
        );
    }

    public function paymentStore(): void
    {
        $this->guard('payments.manage');
        $this->csrfCheck();

        $amount = (float)($_POST['amount'] ?? 0);
$type = strtolower($this->val('transaction_type', 20));


if ($amount <= 0) {
    exit('Amount must be greater than zero.');
}

if (!in_array($type, ['payment', 'refund'], true)) {
    exit('Invalid transaction type. Please select Payment or Refund.');
}

        $q = $this->db->prepare(
            "INSERT INTO payment_transactions
            (booking_id,transaction_type,amount,currency,payment_method,transaction_reference,transaction_date,status,notes,created_by)
            VALUES(?,?,?,?,?,?,?,?,?,?)"
        );

        $q->execute([
            (int)($_POST['booking_id'] ?? 0),
            $type,
            $amount,
            strtoupper($this->val('currency', 3) ?: 'INR'),
            $this->val('payment_method', 50),
            $this->val('transaction_reference', 120),
            $this->val('transaction_date', 10),
            $this->val('status', 20),
            $this->val('notes'),
            $this->uid()
        ]);

        $this->audit('create', 'payment', (int)$this->db->lastInsertId());
        $this->go('/admin/payments');
    }

    public function packageArchiveDelete(): void
    {
        $this->guard('packages.manage');
        $this->csrfCheck();

        $id = $this->id();

        $q = $this->db->prepare(
            "SELECT
            (SELECT COUNT(*) FROM quotations WHERE package_id=?)
            +
            (SELECT COUNT(*) FROM bookings WHERE package_id=?)"
        );

        $q->execute([$id, $id]);

        if ((int)$q->fetchColumn() > 0) {
            $q = $this->db->prepare(
                "UPDATE packages SET status='archived',updated_at=NOW() WHERE id=?"
            );

            $q->execute([$id]);
            $this->audit('archive', 'package', $id);
            $this->go('/admin/packages');
        }

        $q = $this->db->prepare("DELETE FROM packages WHERE id=?");
        $q->execute([$id]);

        $this->audit('delete', 'package', $id);
        $this->go('/admin/packages');
    }




    
}



