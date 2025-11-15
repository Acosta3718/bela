<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        self::startSession();
        // Placeholder. Replace with query to funcionarios table.
        if ($email === 'admin@example.com' && $password === 'secret') {
            $_SESSION['user'] = ['name' => 'Administrador', 'role' => 'admin'];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }

    public static function authorize(array $roles): void
    {
        $user = self::user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            header('HTTP/1.1 403 Forbidden');
            echo View::make('errors/403');
            exit;
        }
    }

    protected static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}