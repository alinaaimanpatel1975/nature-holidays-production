
<?php
use App\Core\Auth;

$pageTitle = 'Packages';
require __DIR__ . '/../partials/header.php';

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<div class="packages-heading">
    <div>
        <p class="eyebrow">CATALOGUE MANAGEMENT</p>
        <h1>Packages</h1>
        <p class="muted">Create and maintain the tours shown in your catalogue.</p>
    </div>
    <a class="package-btn" href="<?= $e($base) ?>/admin/packages/create">＋ New package</a>
</div>

<form class="package-filters" method="GET" action="<?= $e($base) ?>/admin/packages">
    <input
        type="search"
        name="q"
        value="<?= $e($search) ?>"
        placeholder="Search name, slug or description"
    >

    <select name="status">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $item): ?>
            <option value="<?= $e($item) ?>" <?= $status === $item ? 'selected' : '' ?>>
                <?= $e(ucfirst($item)) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="package-btn">Apply filters</button>
    <a class="clear-link" href="<?= $e($base) ?>/admin/packages">Clear</a>
</form>

<section class="package-panel">
    <div class="panel-heading">
        <div>
            <h2>All packages</h2>
            <p class="muted">Manage your available tour listings.</p>
        </div>
        <span class="package-count"><?= count($packages) ?> records</span>
    </div>

    <?php if (!$packages): ?>
        <div class="package-empty">
            <h3>No packages found</h3>
            <p>Create a package or adjust your search filters.</p>
            <a class="package-btn" href="<?= $e($base) ?>/admin/packages/create">Create a package</a>
        </div>
    <?php else: ?>
        <div class="package-table-wrap">
            <table class="package-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Category</th>
                        <th>Destinations</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $item): ?>
                        <tr>
                            <td>
                                <strong><?= $e($item['name']) ?></strong>
                                <span class="package-slug"><?= $e($item['slug']) ?></span>
                                <?php if ((int) $item['is_featured'] === 1): ?>
                                    <span class="package-featured">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $e($item['category_name'] ?: '—') ?></td>
                            <td><?= $e($item['destination_names'] ?: '—') ?></td>
                            <td>
                                <?= $item['duration_days'] !== null ? (int) $item['duration_days'] . ' days' : '—' ?>
                                <?= $item['duration_nights'] !== null ? ' / ' . (int) $item['duration_nights'] . ' nights' : '' ?>
                            </td>
                            <td><span class="package-status"><?= $e($item['status']) ?></span></td>
                            <td><?= $e($item['updated_at']) ?></td>
                            <td>
                                <div class="package-actions">
                                    <a href="<?= $e($base) ?>/admin/packages/view?id=<?= (int) $item['id'] ?>">View</a>
                                    <a href="<?= $e($base) ?>/admin/packages/edit?id=<?= (int) $item['id'] ?>">Edit</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<style>
.packages-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;margin-bottom:24px}
.packages-heading h1{margin:5px 0;font-size:28px}
.eyebrow{font-size:11px;letter-spacing:1.5px;font-weight:700;color:var(--muted)}
.muted{color:var(--muted)}
.package-btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 16px;border:0;border-radius:9px;background:var(--green);color:white;text-decoration:none;font:inherit;font-weight:600;cursor:pointer}
.package-filters{display:flex;gap:10px;flex-wrap:wrap;margin:20px 0 24px}
.package-filters input{flex:1;min-width:220px}
.package-filters input,.package-filters select{padding:12px 14px;border:1px solid var(--line);border-radius:9px;background:white;color:var(--ink);font:inherit}
.clear-link{padding:11px;color:var(--muted);text-decoration:none}
.package-panel{background:white;border:1px solid var(--line);border-radius:14px;overflow:hidden}
.panel-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:20px 22px;border-bottom:1px solid var(--line)}
.panel-heading h2{margin:0 0 5px;font-size:17px}
.panel-heading p{margin:0;font-size:13px}
.package-count{padding:6px 10px;border-radius:20px;background:var(--paper);font-size:12px;color:var(--muted)}
.package-table{width:100%;border-collapse:collapse}
.package-table th,.package-table td{text-align:left;padding:15px 16px;border-bottom:1px solid var(--line);vertical-align:top;font-size:13px}
.package-table th{background:var(--paper);color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.6px;white-space:nowrap}
.package-table tr:last-child td{border-bottom:0}
.package-slug{display:block;margin-top:5px;color:var(--muted);font-size:11px}
.package-featured,.package-status{display:inline-block;margin-top:6px;padding:5px 9px;border-radius:20px;background:var(--green-soft);color:var(--green-dark);font-size:11px}
.package-actions{display:flex;gap:12px;white-space:nowrap}
.package-actions a{color:var(--green);font-weight:600;text-decoration:none}
.package-empty{text-align:center;padding:48px 20px}
.package-empty p{color:var(--muted);margin-bottom:20px}
@media(max-width:850px){.package-table-wrap{overflow-x:auto}.package-table{min-width:850px}}
@media(max-width:600px){.packages-heading h1{font-size:24px}.package-filters{flex-direction:column}.package-filters input,.package-filters select{width:100%}.package-filters .package-btn{width:100%}.panel-heading{padding:16px}}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
