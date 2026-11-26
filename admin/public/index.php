<?php
declare(strict_types=1);

// Allow the local React development server.
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

try {
    $router = require __DIR__ . '/../app/Core/bootstrap.php';
    $router->dispatch();
} catch (Throwable $error) {
    error_log($error->getMessage());

    http_response_code(500);
    echo 'Something went wrong. Please try again later.';
}