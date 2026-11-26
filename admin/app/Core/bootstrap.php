<?php

$config = require __DIR__ . '/../../config/app.php';

define('APP_BASE_PATH', $config['base_path']);

// Load the database class before authentication uses it.
require_once __DIR__ . '/Database.php';


// Automatically load application classes.
spl_autoload_register(function ($class) {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/../' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

\App\Core\Session::start();

$router = new \App\Core\Router();

require __DIR__ . '/../../routes/web.php';

return $router;
