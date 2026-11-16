<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function attempt(string $matricule, string $password): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM administrators WHERE matricule = ? LIMIT 1'
        );
        $statement->execute([$matricule]);
        $admin = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin'] = [
            'id' => (int) $admin['id'],
            'matricule' => $admin['matricule'],
            'first_name' => $admin['first_name'],
            'last_name' => $admin['last_name'],
            'email' => $admin['email'],
            'role' => $admin['role'],
        ];

        $update = Database::connection()->prepare(
            'UPDATE administrators SET last_login_at = NOW() WHERE id = ?'
        );
        $update->execute([$admin['id']]);

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::set('error', 'Connectez-vous pour accéder à votre espace.');
            redirect(url('login'));
        }
    }

    public static function tooManyAttempts(): bool
    {
        $attempts = $_SESSION['_login_attempts'] ?? [];
        $attempts = array_values(array_filter(
            $attempts,
            static fn (int $timestamp): bool => $timestamp > time() - 600
        ));
        $_SESSION['_login_attempts'] = $attempts;

        return count($attempts) >= 5;
    }

    public static function recordFailure(): void
    {
        $_SESSION['_login_attempts'][] = time();
    }

    public static function clearFailures(): void
    {
        unset($_SESSION['_login_attempts']);
    }
}
