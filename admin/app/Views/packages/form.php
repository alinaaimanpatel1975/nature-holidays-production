
<?php
use App\Core\Csrf;

$pageTitle = ($mode === 'edit' ? 'Edit Package' : 'Create Package');
require __DIR__ . '/../partials/header.php';

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$isEdit = $mode === 'edit';
$action = $isEdit ? $base . '/admin/packages/update' : $base . '/admin/packages/store';

$selected = array_map('intval', $selectedDestinations ?? []);
$currentImage = trim((string) ($package['image'] ?? ''));

if ($currentImage !== '' && !preg_match('~^https?://~i', $currentImage)) {
    $previewImage = $base . '/' . ltrim($currentImage, '/');
} else {
    $previewImage = $currentImage;
}
?>

<div class="package-form-heading">
    <div>
        <p class="package-eyebrow">CATALOGUE MANAGEMENT</p>
        <h1><?= $isEdit ? 'Edit package' : 'Create a package' ?></h1>
        <p class="package-muted">
            <?= $isEdit
                ? 'Update the details and information for this tour.'
                : 'Add a new tour to the Nature Holidays catalogue.' ?>
        </p>
    </div>

    <a class="package-back" href="<?= $e($base) ?>/admin/packages">
        ← Back to packages
    </a>
</div>

<form
    class="package-form"
    method="POST"
    action="<?= $e($action) ?>"
    enctype="multipart/form-data"
>
    <input type="hidden" name="_csrf" value="<?= $e(Csrf::token()) ?>">

    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) ($package['id'] ?? 0) ?>">
    <?php endif; ?>

    <section class="package-form-card">
        <div class="package-section-heading">
            <div>
                <h2>Basic information</h2>
                <p>Give your tour a name and describe what travellers can expect.</p>
            </div>
        </div>

        <div class="package-grid">
            <div class="package-field">
                <label for="name">Package name <span>*</span></label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    maxlength="200"
                    required
                    value="<?= $e($package['name'] ?? '') ?>"
                    placeholder="e.g. Kerala Backwaters Escape"
                >
            </div>

            <div class="package-field">
                <label for="slug">URL slug <span>*</span></label>
                <input
                    id="slug"
                    type="text"
                    name="slug"
                    maxlength="220"
                    required
                    value="<?= $e($package['slug'] ?? '') ?>"
                    placeholder="kerala-backwaters-escape"
                >
            </div>

            <div class="package-field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Choose a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= (int) $category['id'] ?>"
                            <?= (string) ($package['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>
                        >
                            <?= $e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (empty($categories)): ?>
                    <small>No categories are available yet. Add categories before assigning one.</small>
                <?php endif; ?>
            </div>

            <div class="package-field">
                <label for="short_description">Short description</label>
                <input
                    id="short_description"
                    type="text"
                    name="short_description"
                    maxlength="500"
                    value="<?= $e($package['short_description'] ?? '') ?>"
                    placeholder="A short introduction to this tour"
                >
                <small>Keep this concise. It can be used in package cards.</small>
            </div>

            <div class="package-field package-wide">
                <label for="description">Full description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    placeholder="Describe the experience, itinerary, highlights and inclusions..."
                ><?= $e($package['description'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <section class="package-form-card">
        <div class="package-section-heading">
            <div>
                <h2>Duration and pricing</h2>
                <p>Add the indicative duration and starting price, if available.</p>
            </div>
        </div>

        <div class="package-grid">
            <div class="package-field">
                <label for="duration_days">Duration (days)</label>
                <input
                    id="duration_days"
                    type="number"
                    name="duration_days"
                    min="1"
                    max="365"
                    value="<?= $e($package['duration_days'] ?? '') ?>"
                    placeholder="e.g. 5"
                >
            </div>

            <div class="package-field">
                <label for="duration_nights">Duration (nights)</label>
                <input
                    id="duration_nights"
                    type="number"
                    name="duration_nights"
                    min="0"
                    max="364"
                    value="<?= $e($package['duration_nights'] ?? '') ?>"
                    placeholder="e.g. 4"
                >
            </div>

            <div class="package-field">
                <label for="indicative_price">Indicative price</label>
                <input
                    id="indicative_price"
                    type="number"
                    name="indicative_price"
                    min="0"
                    step="0.01"
                    value="<?= $e($package['indicative_price'] ?? '') ?>"
                    placeholder="e.g. 12500"
                >
                <small>This is an indicative amount, not an online payment.</small>
            </div>

            <div class="package-field">
                <label for="currency">Currency</label>
                <select id="currency" name="currency">
                    <?php foreach ($currencies as $currency): ?>
                        <option
                            value="<?= $e($currency) ?>"
                            <?= ($package['currency'] ?? 'INR') === $currency ? 'selected' : '' ?>
                        >
                            <?= $e($currency) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <section class="package-form-card">
        <div class="package-section-heading">
            <div>
                <h2>Package image</h2>
                <p>Upload a new image or provide an existing image path or URL.</p>
            </div>
        </div>

        <div class="package-grid">
            <div class="package-field package-wide">
                <label for="image_file">Upload image</label>
                <div class="package-upload-box">
                    <input
                        id="image_file"
                        type="file"
                        name="image_file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >
                    <small>Accepted formats: JPG, JPEG, PNG and WebP. Maximum size: 5 MB.</small>
                </div>

                <div id="imagePreview" class="package-image-preview" style="display:none">
                    <img id="previewImage" src="" alt="Package image preview">
                    <p id="previewCaption"></p>
                </div>
            </div>

            <div class="package-field package-wide">
                <label for="image">Image path or URL</label>
                <input
                    id="image"
                    type="text"
                    name="image"
                    maxlength="255"
                    value="<?= $e($currentImage) ?>"
                    placeholder="images/packages/kerala.jpg or https://..."
                >
                <small>If a new image is uploaded, the uploaded image takes priority.</small>
            </div>
        </div>
    </section>

    <section class="package-form-card">
        <div class="package-section-heading">
            <div>
                <h2>Destinations</h2>
                <p>Select the destinations included in this package.</p>
            </div>
        </div>

        <?php if (!empty($destinations)): ?>
            <div class="package-destinations">
                <?php foreach ($destinations as $destination): ?>
                    <label class="package-destination-option">
                        <input
                            type="checkbox"
                            name="destination_ids[]"
                            value="<?= (int) $destination['id'] ?>"
                            <?= in_array((int) $destination['id'], $selected, true) ? 'checked' : '' ?>
                        >
                        <span>
                            <strong><?= $e($destination['name']) ?></strong>
                            <?php if (!empty($destination['state']) || !empty($destination['country'])): ?>
                                <small>
                                    <?= $e(implode(' · ', array_filter([
                                        $destination['state'] ?? '',
                                        $destination['country'] ?? ''
                                    ]))) ?>
                                </small>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="package-muted">No destinations are available yet. Add destinations before assigning them to this package.</p>
        <?php endif; ?>
    </section>

    <section class="package-form-card">
        <div class="package-section-heading">
            <div>
                <h2>Publishing settings</h2>
                <p>Control the package's catalogue status and featured placement.</p>
            </div>
        </div>

        <div class="package-grid">
            <div class="package-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach ($statuses as $item): ?>
                        <option
                            value="<?= $e($item) ?>"
                            <?= ($package['status'] ?? 'draft') === $item ? 'selected' : '' ?>
                        >
                            <?= $e(ucfirst($item)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="package-field package-featured-field">
                <label class="package-featured-toggle">
                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        <?= (int) ($package['is_featured'] ?? 0) === 1 ? 'checked' : '' ?>
                    >
                    <span>
                        <strong>Featured package</strong>
                        <small>Mark this package for featured placement.</small>
                    </span>
                </label>
            </div>
        </div>
    </section>

    <div class="package-form-actions">
        <a class="package-cancel" href="<?= $e($base) ?>/admin/packages">Cancel</a>
        <button type="submit" class="package-submit">
            <?= $isEdit ? 'Save changes' : 'Create package' ?>
        </button>
    </div>
</form>

<style>
.package-form-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;margin-bottom:24px}
.package-form-heading h1{margin:5px 0;font-size:28px}
.package-eyebrow{font-size:11px;letter-spacing:1.5px;font-weight:700;color:var(--muted)}
.package-muted{color:var(--muted)}
.package-back{color:var(--green);text-decoration:none;font-weight:600;padding:10px 0}
.package-form{display:grid;gap:18px}
.package-form-card{background:white;border:1px solid var(--line);border-radius:14px;padding:24px}
.package-section-heading{margin-bottom:22px}
.package-section-heading h2{font-size:17px;margin:0 0 6px}
.package-section-heading p{font-size:13px;color:var(--muted);margin:0}
.package-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
.package-field{display:flex;flex-direction:column;gap:8px;min-width:0}
.package-field label{font-size:13px;font-weight:600;color:var(--ink)}
.package-field label span{color:var(--red)}
.package-field input:not([type="checkbox"]):not([type="file"]),.package-field select,.package-field textarea{width:100%;padding:12px 13px;border:1px solid var(--line);border-radius:9px;background:white;color:var(--ink);font:inherit;font-size:13px;outline:none}
.package-field input:focus,.package-field select:focus,.package-field textarea:focus{border-color:var(--green)}
.package-field textarea{resize:vertical;line-height:1.6}
.package-field small{font-size:11px;line-height:1.5;color:var(--muted)}
.package-wide{grid-column:1/-1}
.package-upload-box{padding:17px;border:1px dashed #bdcbbd;border-radius:10px;background:var(--paper)}
.package-upload-box input{max-width:100%;font-size:13px}
.package-upload-box small{display:block;margin-top:9px}
.package-image-preview{margin-top:14px}
.package-image-preview img{display:block;width:min(100%,380px);max-height:220px;object-fit:cover;border-radius:10px;border:1px solid var(--line)}
.package-image-preview p{font-size:12px;color:var(--muted)}
.package-destinations{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.package-destination-option{display:flex;align-items:flex-start;gap:11px;padding:13px;border:1px solid var(--line);border-radius:10px;cursor:pointer}
.package-destination-option input,.package-featured-toggle input{accent-color:var(--green);margin-top:3px}
.package-destination-option span,.package-featured-toggle span{display:flex;flex-direction:column;gap:4px}
.package-destination-option strong,.package-featured-toggle strong{font-size:13px}
.package-destination-option small,.package-featured-toggle small{font-size:11px;color:var(--muted)}
.package-featured-field{justify-content:center}
.package-featured-toggle{display:flex;gap:11px;align-items:flex-start;padding:15px;border:1px solid var(--line);border-radius:10px;cursor:pointer}
.package-form-actions{display:flex;justify-content:flex-end;align-items:center;gap:12px;padding:4px 0 20px}
.package-cancel{padding:11px 15px;color:var(--muted);text-decoration:none;font-weight:600}
.package-submit{padding:12px 19px;border:0;border-radius:9px;background:var(--green);color:white;font:inherit;font-weight:600;cursor:pointer}
.package-submit:hover{background:var(--green-dark)}
@media(max-width:650px){.package-form-heading h1{font-size:24px}.package-form-card{padding:18px}.package-grid,.package-destinations{grid-template-columns:1fr}.package-wide{grid-column:auto}.package-form-actions{flex-direction:column-reverse;align-items:stretch}.package-submit,.package-cancel{text-align:center}.package-submit{width:100%}}
</style>

<script>
(function () {
    const fileInput = document.getElementById('image_file');
    const previewBox = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const previewCaption = document.getElementById('previewCaption');

    const existingImage = <?= json_encode(
        $previewImage,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    function showPreview(source, caption) {
        previewImage.src = source;
        previewCaption.textContent = caption;
        previewBox.style.display = 'block';
    }

    if (existingImage) {
        showPreview(existingImage, 'Current package image');
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                alert('Please select a JPG, PNG or WebP image.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('The image must be smaller than 5 MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                showPreview(event.target.result, 'New image selected: ' + file.name);
            };

            reader.readAsDataURL(file);
        });
    }
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
