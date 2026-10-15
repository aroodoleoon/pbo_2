<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;
use PosClient\Http;

class AuthController
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . (Http::isManager() ? '?page=reports' : '?page=pos'));
            exit;
        }

        require __DIR__ . '/../../views/login.php';
    }

    public function login(): void
    {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));

        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Username dan password wajib diisi.');
            header('Location: ?page=login');
            exit;
        }

        $response = $this->api->post('/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if (!$response->isSuccess()) {
            Session::setFlash('error', $response->getErrorMessage() ?? 'Login gagal. Kredensial tidak valid.');
            header('Location: ?page=login');
            exit;
        }

        $data = $response->getData();
        $user = $data['user'] ?? [
            'id' => '11111111-1111-1111-1111-111111111111',
            'name' => $username,
            'username' => $username,
            'role' => (($_POST['role'] ?? '') === 'manager' || str_contains(strtolower($username), 'admin')) ? 'ADMIN' : 'CASHIER'
        ];

        // Peran yang dipilih di form harus cocok dengan peran akun di backend
        $apiRole = strtoupper((string)($user['role'] ?? ''));
        $realRole = ($apiRole === 'ADMIN' || $apiRole === 'MANAGER') ? 'manager' : 'kasir';
        $chosen = (string)($_POST['role'] ?? '');
        if (($user['active'] ?? true) === false) {
            Session::setFlash('error', 'Akun ini sudah dinonaktifkan.');
            header('Location: ?page=login');
            exit;
        }
        if ($chosen !== '' && $chosen !== $realRole) {
            Session::setFlash('error', 'Akun ini terdaftar sebagai ' . ucfirst($realRole) . '. Pilih peran yang sesuai.');
            header('Location: ?page=login');
            exit;
        }

        session_regenerate_id(true);
        Session::set('accessToken', $data['accessToken'] ?? 'DUMMY-TOKEN');
        Session::set('user', $user);

        Session::setFlash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['name'] ?? $username));
        header('Location: ' . ($realRole === 'manager' ? '?page=reports' : '?page=pos'));
        exit;
    }

    public function logout(): void
    {
        // Panggil endpoint logout di Ktor
        $this->api->post('/auth/logout');
        Session::clear();
        Session::setFlash('info', 'Anda telah berhasil logout.');
        header('Location: ?page=login');
        exit;
    }
}

