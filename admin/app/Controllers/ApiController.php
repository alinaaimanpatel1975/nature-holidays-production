<?php

namespace App\Controllers;

use App\Models\Package;
use Throwable;

class ApiController
{
    public function packages(): void
    {
        $this->respond(function () {
            return (new Package())->all('', 'published');
        }, 'Unable to load packages.');
    }

    public function destinations(): void
    {
        $this->respond(function () {
            return (new Package())->destinations();
        }, 'Unable to load destinations.');
    }

    public function categories(): void
    {
        $this->respond(function () {
            return (new Package())->categories();
        }, 'Unable to load categories.');
    }

    private function respond(callable $callback, string $errorMessage): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            echo json_encode([
                'success' => true,
                'data' => $callback(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } catch (Throwable $error) {
            error_log('Public API error: ' . $error->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => $errorMessage,
            ]);
        }
    }
}
