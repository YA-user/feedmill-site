<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = :email AND active = 1 LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }

    public static function require(): void
    {
        if (!self::check()) {
            \flash('error', 'Сначала войдите в раздел администратора.');
            \redirect('/admin/login');
        }
    }

    public static function hasRole(string $role): bool
    {
        return (self::user()['role'] ?? null) === $role;
    }

    public static function canAccessSection(string $sectionKey): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if (($user['role'] ?? '') === 'admin') {
            return true;
        }

        $stmt = Database::pdo()->prepare('SELECT 1 FROM user_permissions WHERE user_id = :user_id AND section_key = :section_key LIMIT 1');
        $stmt->execute([
            'user_id' => (int)$user['id'],
            'section_key' => $sectionKey,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public static function permittedSectionKeys(): array
    {
        $user = self::user();
        if (!$user || ($user['role'] ?? '') === 'admin') {
            return [];
        }

        $stmt = Database::pdo()->prepare('SELECT section_key FROM user_permissions WHERE user_id = :user_id ORDER BY section_key');
        $stmt->execute(['user_id' => (int)$user['id']]);
        return array_column($stmt->fetchAll(), 'section_key');
    }
}
