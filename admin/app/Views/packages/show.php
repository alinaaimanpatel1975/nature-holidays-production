<?php
use App\Core\Auth;

$pageTitle = 'Package Details';
require __DIR__ . '/../partials/header.php';

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">PACKAGE MANAGEMENT</p>
        <h1><?= $e($package['name']) ?></h1>
        <p class="muted"><?= $e($package['slug']) ?></p>
    </div>
    <div class="actions">
        <a class="btn" href="<?= $e($base) ?>/admin/packages/edit?id=<?= (int) $package['id'] ?>">Edit package</a>
        <a class="btn btn-secondary" href="<?= $e($base) ?>/admin/packages">Back to packages</a>
    </div>
</div>

<section class="card">
    <div class="actions">
        <span class="pill"><?= $e($package['status']) ?></span>
        <?php if ((int) $package['is_featured'] === 1): ?>
            <span class="pill">Featured</span>
        <?php endif; ?>
    </div>

    <h2>Overview</h2>
    <p><?= $e($package['short_description'] ?: 'No short description added.') ?></p>

    <div class="meta-grid">
        <div><small>Category</small><strong><?= $e($package['category_name'] ?: '—') ?></strong></div>
        <div>
            <small>Duration</small>
            <strong>
                <?= $package['duration_days'] !== null ? (int) $package['duration_days'] . ' days' : '—' ?>
                <?= $package['duration_nights'] !== null ? ' / ' . (int) $package['duration_nights'] . ' nights' : '' ?>
            </strong>
        </div>
        <div>
            <small>Indicative price</small>
            <strong>
                <?= $package['indicative_price'] !== null
                    ? $e($package['currency']) . ' ' . number_format((float) $package['indicative_price'], 2)
                    : 'Not specified' ?>
            </strong>
        </div>
        <div><small>Image path / URL</small><strong><?= $e($package['image'] ?: 'Not specified') ?></strong></div>
    </div>

    <h2>Assigned destinations</h2>
    <?php if (!empty($package['destinations'])): ?>
        <ul>
            <?php foreach ($package['destinations'] as $destination): ?>
                <li>
                    <?= $e($destination['name']) ?>
                    <?= !empty($destination['state']) ? ' · ' . $e($destination['state']) : '' ?>
                    <?= !empty($destination['country']) ? ' · ' . $e($destination['country']) : '' ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">No destinations assigned.</p>
    <?php endif; ?>

    <h2>Full description</h2>
    <div class="description">
        <?= nl2br($e($package['description'] ?: 'No full description added.')) ?>
    </div>

    <div class="record-meta">
        <span>Created: <?= $e($package['created_at']) ?></span>
        <span>Updated: <?= $e($package['updated_at']) ?></span>
    </div>
</section>

<style>
.page-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;margin-bottom:24px}
.eyebrow{font-size:11px;letter-spacing:1.5px;font-weight:700;color:var(--muted)}
.page-heading h1{margin:5px 0;font-size:28px}
.muted{color:var(--muted)}
.actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn{display:inline-block;padding:11px 16px;background:var(--green);color:white;border-radius:9px;text-decoration:none;font-weight:600}
.btn-secondary{background:white;color:var(--ink);border:1px solid var(--line)}
.meta-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:24px 0}
.meta-grid div{padding:17px;background:var(--paper);border:1px solid var(--line);border-radius:10px;overflow-wrap:anywhere}
.meta-grid small{display:block;color:var(--muted);margin-bottom:8px}
.meta-grid strong{font-size:14px}
.pill{display:inline-block;padding:6px 11px;border-radius:30px;background:var(--green-soft);color:var(--green-dark);font-size:12px}
.description{white-space:pre-wrap;line-height:1.8}
.record-meta{display:flex;gap:20px;flex-wrap:wrap;margin-top:28px;padding-top:18px;border-top:1px solid var(--line);font-size:12px;color:var(--muted)}
@media(max-width:650px){.meta-grid{grid-template-columns:1fr}.page-heading h1{font-size:23px}}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>