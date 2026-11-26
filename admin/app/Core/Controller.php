<?php
namespace App\Core;

class Controller
{
    protected function view(string $view, array $viewData = []): void
    {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($viewData, EXTR_SKIP);
        require $viewPath;
    }
}