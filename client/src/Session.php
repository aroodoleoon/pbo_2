<?php
declare(strict_types=1);

namespace PosClient;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash'] = [
            'type' => $type, // success, error, warning, info
            'message' => $message
        ];
    }

    public static function getFlash(): ?array
    {
        self::start();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function getAuthUser(): ?array
    {
        return self::get('user');
    }

    public static function getToken(): ?string
    {
        return self::get('accessToken');
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user') && self::has('accessToken');
    }
}

