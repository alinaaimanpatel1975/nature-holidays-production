<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: ' . APP_BASE_PATH . '/admin');
            exit;
        }

        $this->view('auth/login');
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Please refresh the login page.');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (
            !is_string($email) ||
            !is_string($password) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $password === ''
        ) {
            $this->view('auth/login', [
                'error' => 'Enter a valid email and password.',
            ]);
            return;
        }

        if (!Auth::attempt($email, $password)) {
            $this->view('auth/login', [
                'error' => 'Invalid login details or inactive account.',
            ]);
            return;
        }

        header('Location: ' . APP_BASE_PATH . '/admin');
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Invalid request.');
        }

        Auth::logout();

        header('Location: ' . APP_BASE_PATH . '/login');
        exit;
    }
}