<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;

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
            header('Location: ?page=pos');
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
        Session::set('accessToken', $data['accessToken'] ?? 'DUMMY-TOKEN');
        Session::set('user', $data['user'] ?? [
            'id' => '11111111-1111-1111-1111-111111111111',
            'name' => $username,
            'username' => $username,
            'role' => 'CASHIER'
        ]);

        Session::setFlash('success', 'Selamat datang kembali, ' . htmlspecialchars($data['user']['name'] ?? $username));
        header('Location: ?page=pos');
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

