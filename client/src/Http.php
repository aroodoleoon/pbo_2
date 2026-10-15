<?php
declare(strict_types=1);

namespace PosClient;

/** Helper kecil untuk redirect aman dan pengecekan peran. */
class Http
{
    private const BACK_OK = [
        '?page=pos#produk', '?page=pos#katalog', '?page=pos#member',
        '?page=inventory', '?page=customers',
    ];

    public static function isManager(): bool
    {
        $role = strtoupper((string)(Session::getAuthUser()['role'] ?? ''));
        return $role === 'ADMIN' || $role === 'MANAGER';
    }

    public static function isCashier(): bool
    {
        return Session::isLoggedIn() && !self::isManager();
    }

    /** Redirect ke halaman asal form (whitelist), lalu berhenti. Kasir selalu kembali ke panel di halaman POS. */
    public static function back(string $default): void
    {
        $back = (string)($_POST['back'] ?? '');
        if (!in_array($back, self::BACK_OK, true)) {
            $back = $default;
        }
        if (self::isCashier()) {
            $back = match ($back) {
                '?page=inventory' => '?page=pos#katalog',
                '?page=customers' => '?page=pos#member',
                default => $back,
            };
        }
        header('Location: ' . $back);
        exit;
    }
}
