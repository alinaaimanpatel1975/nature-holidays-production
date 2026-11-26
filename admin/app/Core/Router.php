<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

        $basePath = rtrim(APP_BASE_PATH, '/');

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        $action = $this->routes[$method][$uri] ?? null;

        if (!$action) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        if (is_array($action)) {
            [$controller, $method] = $action;
            (new $controller())->$method();
            return;
        }

        $action();
    }
}