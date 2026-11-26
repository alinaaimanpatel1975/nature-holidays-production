<?php

namespace App\Core;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $pdo = \Database::connect();

        $stmt = $pdo->prepare("
            SELECT
                u.id,
                u.name,
                u.email,
                u.password,
                u.status,
                r.name AS role_name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
            LIMIT 1
        ");

        $stmt->execute([strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (
            !$user ||
            $user['status'] !== 'active' ||
            !password_verify($password, $user['password'])
        ) {
            return false;
        }

        $permissionQuery = $pdo->prepare("
            SELECT p.name
            FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = (
                SELECT role_id FROM users WHERE id = ?
            )
        ");

        $permissionQuery->execute([$user['id']]);

        $permissions = $permissionQuery->fetchAll(
            \PDO::FETCH_COLUMN
        );

        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role_name'],
            'permissions' => $permissions,
        ];

        $update = $pdo->prepare(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?"
        );
        $update->execute([$user['id']]);

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['auth']['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function can(string $permission): bool
    {
        return self::check() &&
            in_array(
                $permission,
                $_SESSION['auth']['permissions'] ?? [],
                true
            );
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}