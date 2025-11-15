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

    public static function roles(): array
    {
        $user = self::user();
        return $user['roles'] ?? [];
    }

    public static function attempt(string $email, string $password): bool
    {
        self::startSession();

        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT f.*, GROUP_CONCAT(DISTINCT r.nombre) AS roles'
            . ' FROM funcionarios f'
            . ' LEFT JOIN usuario_roles ur ON ur.funcionario_id = f.id'
            . ' LEFT JOIN roles r ON r.id = ur.rol_id'
            . ' WHERE f.email = :email AND f.activo = 1'
            . ' GROUP BY f.id'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || empty($user['password'])) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        $roles = [];
        if (!empty($user['roles'])) {
            $roles = array_values(array_filter(array_map('trim', explode(',', $user['roles']))));
        }

        if (!$roles && !empty($user['rol'])) {
            $roles[] = $user['rol'];
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['nombre'],
            'email' => $user['email'],
            'role' => $roles[0] ?? $user['rol'] ?? null,
            'roles' => $roles,
        ];

        return true;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        session_regenerate_id(true);
        session_destroy();
    }

    public static function authorize(array $roles): void
    {
        if (!self::check()) {
            redirect('/login');
        }

        $userRoles = array_map('strtolower', self::roles());
        $required = array_map('strtolower', $roles);

        if (!$required) {
            return;
        }

        if (!array_intersect($userRoles, $required)) {
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