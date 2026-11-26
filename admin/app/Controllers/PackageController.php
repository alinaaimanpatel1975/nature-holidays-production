<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Package;

class PackageController extends Controller
{
    private Package $packages;

    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct()
    {
        $this->packages = new Package();
    }

    public function index(): void
    {
        $this->authorize();

        $search = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $this->view('packages/index', [
            'user' => Auth::user(),
            'packages' => $this->packages->all($search, $status),
            'search' => $search,
            'status' => $status,
            'statuses' => Package::statuses(),
        ]);
    }

    public function create(): void
    {
        $this->authorize();

        $this->view('packages/form', [
            'mode' => 'create',
            'package' => $this->emptyPackage(),
            'categories' => $this->packages->categories(),
            'destinations' => $this->packages->destinations(),
            'statuses' => Package::statuses(),
            'currencies' => Package::currencies(),
            'selectedDestinations' => [],
            'error' => null,
        ]);
    }

    public function store(): void
    {
        $this->authorize();
        $this->verifyCsrf();

        [$data, $destinationIds, $error] = $this->validatedInput();

        if ($error === null) {
            [$uploadedImage, $uploadError] = $this->uploadPackageImage();

            if ($uploadError !== null) {
                $error = $uploadError;
            } elseif ($uploadedImage !== null) {
                $data['image'] = $uploadedImage;
            }
        }

        if ($error !== null) {
            $this->renderForm('create', $data, $destinationIds, $error);
            return;
        }

        try {
            $id = $this->packages->create($data, $destinationIds);
            $this->redirect('/admin/packages/view?id=' . $id);
        } catch (\PDOException $error) {
            error_log($error->getMessage());

            $this->renderForm(
                'create',
                $data,
                $destinationIds,
                'The package could not be saved. Check that its slug is unique and try again.'
            );
        }
    }

    public function show(): void
    {
        $this->authorize();

        $id = $this->requestId();
        $package = $this->packages->find($id);

        if (!$package) {
            $this->notFound();
            return;
        }

        $this->view('packages/show', [
            'package' => $package,
        ]);
    }

    public function edit(): void
    {
        $this->authorize();

        $id = $this->requestId();
        $package = $this->packages->find($id);

        if (!$package) {
            $this->notFound();
            return;
        }

        $this->view('packages/form', [
            'mode' => 'edit',
            'package' => $package,
            'categories' => $this->packages->categories(),
            'destinations' => $this->packages->destinations(),
            'statuses' => Package::statuses(),
            'currencies' => Package::currencies(),
            'selectedDestinations' => array_map(
                static fn(array $destination): int => (int) $destination['id'],
                $package['destinations']
            ),
            'error' => null,
        ]);
    }

    public function update(): void
    {
        $this->authorize();
        $this->verifyCsrf();

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $this->notFound();
            return;
        }

        $existingPackage = $this->packages->find((int) $id);

        if (!$existingPackage) {
            $this->notFound();
            return;
        }

        [$data, $destinationIds, $error] = $this->validatedInput(
            (int) $id,
            (string) ($existingPackage['image'] ?? '')
        );

        if ($error === null) {
            [$uploadedImage, $uploadError] = $this->uploadPackageImage();

            if ($uploadError !== null) {
                $error = $uploadError;
            } elseif ($uploadedImage !== null) {
                $data['image'] = $uploadedImage;
            }
        }

        if ($error !== null) {
            $this->renderForm(
                'edit',
                array_merge($data, ['id' => (int) $id]),
                $destinationIds,
                $error
            );
            return;
        }

        try {
            $this->packages->update((int) $id, $data, $destinationIds);
            $this->redirect('/admin/packages/view?id=' . (int) $id);
        } catch (\PDOException $error) {
            error_log($error->getMessage());

            $this->renderForm(
                'edit',
                array_merge($data, ['id' => (int) $id]),
                $destinationIds,
                'The package could not be updated. Check that its slug is unique and try again.'
            );
        }
    }

    /**
     * Upload and validate a package image.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function uploadPackageImage(): array
    {
        if (
            !isset($_FILES['image_file']) ||
            !is_array($_FILES['image_file'])
        ) {
            return [null, null];
        }

        $file = $_FILES['image_file'];

        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            $message = match ($uploadError) {
                UPLOAD_ERR_INI_SIZE,
                UPLOAD_ERR_FORM_SIZE => 'The image is too large. Maximum size is 5 MB.',
                UPLOAD_ERR_PARTIAL => 'The image upload was incomplete. Please try again.',
                UPLOAD_ERR_NO_TMP_DIR => 'The server is missing its temporary upload folder.',
                UPLOAD_ERR_CANT_WRITE => 'The server could not save the uploaded image.',
                UPLOAD_ERR_EXTENSION => 'The server stopped the image upload.',
                default => 'There was a problem uploading the image.',
            };

            return [null, $message];
        }

        $temporaryPath = $file['tmp_name'] ?? '';

        if (
            !is_string($temporaryPath) ||
            !is_uploaded_file($temporaryPath)
        ) {
            return [null, 'The uploaded image could not be verified.'];
        }

        $fileSize = (int) ($file['size'] ?? 0);

        if ($fileSize < 1 || $fileSize > self::MAX_IMAGE_SIZE) {
            return [null, 'Choose an image smaller than 5 MB.'];
        }

        if (!class_exists(\finfo::class)) {
            return [null, 'The server cannot validate image uploads right now.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($temporaryPath);

        if (
            !is_string($mimeType) ||
            !isset(self::ALLOWED_IMAGE_TYPES[$mimeType])
        ) {
            return [null, 'Only JPG, PNG and WebP images are allowed.'];
        }

        $imageInfo = @getimagesize($temporaryPath);

        if (
            $imageInfo === false ||
            ($imageInfo['mime'] ?? '') !== $mimeType
        ) {
            return [null, 'The uploaded file is not a valid image.'];
        }

        $extension = self::ALLOWED_IMAGE_TYPES[$mimeType];

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $projectRoot = dirname(__DIR__, 2);

        $uploadDirectory = $projectRoot
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . 'packages';

        if (
            !is_dir($uploadDirectory) &&
            !mkdir($uploadDirectory, 0755, true) &&
            !is_dir($uploadDirectory)
        ) {
            return [null, 'The package image folder could not be created.'];
        }

        $destinationPath = $uploadDirectory
            . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($temporaryPath, $destinationPath)) {
            return [null, 'The server could not save the image. Check folder permissions.'];
        }

        return [
            '/assets/images/packages/' . $filename,
            null,
        ];
    }

    private function authorize(): void
    {
        Auth::requireLogin();

        if (!Auth::can('packages.manage')) {
            http_response_code(403);
            exit('You do not have permission to manage packages.');
        }
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Your session expired. Refresh the page and try again.');
        }
    }

    private function requestId(): int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        return $id && $id > 0 ? (int) $id : 0;
    }

    private function validatedInput(
        ?int $ignoreId = null,
        ?string $currentImage = null
    ): array {
        $name = trim((string) ($_POST['name'] ?? ''));

        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        $shortDescription = trim((string) ($_POST['short_description'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $image = trim((string) ($_POST['image'] ?? ''));

        // Keep the existing image if the edit form's image field is blank.
        if ($image === '' && $currentImage !== null) {
            $image = $currentImage;
        }

        $currency = strtoupper(trim((string) ($_POST['currency'] ?? 'INR')));
        $status = trim((string) ($_POST['status'] ?? 'draft'));

        $categoryRaw = $_POST['category_id'] ?? '';
        $categoryId = $categoryRaw === ''
            ? null
            : filter_var($categoryRaw, FILTER_VALIDATE_INT);

        $daysRaw = trim((string) ($_POST['duration_days'] ?? ''));
        $nightsRaw = trim((string) ($_POST['duration_nights'] ?? ''));
        $priceRaw = trim((string) ($_POST['indicative_price'] ?? ''));

        $days = $daysRaw === '' ? null : filter_var($daysRaw, FILTER_VALIDATE_INT);
        $nights = $nightsRaw === '' ? null : filter_var($nightsRaw, FILTER_VALIDATE_INT);

        $price = null;

        if ($priceRaw !== '') {
            $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
        }

        $destinationIds = $_POST['destination_ids'] ?? [];

        if (!is_array($destinationIds)) {
            $destinationIds = [];
        }

        $destinationIds = array_values(array_unique(array_filter(
            array_map(
                static fn($value) => filter_var($value, FILTER_VALIDATE_INT),
                $destinationIds
            ),
            static fn($value) => $value !== false && $value !== null && $value > 0
        )));

        $featured = isset($_POST['is_featured']) ? 1 : 0;

        $error = null;

        if ($name === '' || strlen($name) > 200) {
            $error = 'Enter a package name (maximum 200 characters).';
        } elseif ($slug === '' || strlen($slug) > 220) {
            $error = 'Enter a valid URL slug.';
        } elseif ($this->packages->slugExists($slug, $ignoreId)) {
            $error = 'That URL slug is already in use. Choose another.';
        } elseif (strlen($shortDescription) > 500) {
            $error = 'The short description must be 500 characters or fewer.';
        } elseif (!in_array($currency, Package::currencies(), true)) {
            $error = 'Choose a supported currency.';
        } elseif (!in_array($status, Package::statuses(), true)) {
            $error = 'Choose a valid package status.';
        } elseif (
            $categoryId === false ||
            ($categoryId !== null && !$this->packages->categoryExists((int) $categoryId))
        ) {
            $error = 'Choose a valid active category.';
        } elseif (
            ($daysRaw !== '' && ($days === false || $days < 1 || $days > 365)) ||
            ($nightsRaw !== '' && ($nights === false || $nights < 0 || $nights > 364))
        ) {
            $error = 'Enter a valid duration.';
        } elseif (
            $priceRaw !== '' &&
            ($price === false || $price < 0 || $price > 9999999999.99)
        ) {
            $error = 'Enter a valid non-negative indicative price.';
        } elseif (strlen($image) > 255) {
            $error = 'The image path or URL is too long.';
        }

        $data = [
            'category_id' => $categoryId === null ? null : (int) $categoryId,
            'name' => $name,
            'slug' => $slug,
            'short_description' => $shortDescription !== '' ? $shortDescription : null,
            'description' => $description !== '' ? $description : null,
            'duration_days' => $days === null || $days === false ? null : (int) $days,
            'duration_nights' => $nights === null || $nights === false ? null : (int) $nights,
            'indicative_price' => $price === null || $price === false
                ? null
                : number_format((float) $price, 2, '.', ''),
            'currency' => $currency,
            'image' => $image !== '' ? $image : null,
            'status' => $status,
            'is_featured' => $featured,
        ];

        return [$data, $destinationIds, $error];
    }

    private function renderForm(
        string $mode,
        array $package,
        array $selectedDestinations,
        string $error
    ): void {
        $this->view('packages/form', [
            'mode' => $mode,
            'package' => $package,
            'categories' => $this->packages->categories(),
            'destinations' => $this->packages->destinations(),
            'statuses' => Package::statuses(),
            'currencies' => Package::currencies(),
            'selectedDestinations' => $selectedDestinations,
            'error' => $error,
        ]);
    }

    private function emptyPackage(): array
    {
        return [
            'name' => '',
            'slug' => '',
            'short_description' => '',
            'description' => '',
            'duration_days' => '',
            'duration_nights' => '',
            'indicative_price' => '',
            'currency' => 'INR',
            'image' => '',
            'status' => 'draft',
            'is_featured' => 0,
            'category_id' => null,
        ];
    }

    private function redirect(string $path): never
    {
        header('Location: ' . APP_BASE_PATH . $path);
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../Views/errors/404.php';
    }
}
