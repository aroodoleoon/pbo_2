<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;

class CashierController
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    public function index(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: ?page=login');
            exit;
        }

        $user = Session::getAuthUser();
        if (($user['role'] ?? '') !== 'ADMIN') {
            Session::setFlash('error', 'Akses ditolak: Hanya Administrator yang berwenang mengelola kasir.');
            header('Location: ?page=pos');
            exit;
        }

        $resp = $this->api->get('/employees');
        $rawCashiers = $resp->isSuccess() ? ($resp->getData() ?? []) : [];

        // Format data kasir
        $cashiers = array_map(function ($c) {
            return [
                'id' => $c['id'],
                'name' => $c['name'],
                'username' => $c['username'],
                'role' => $c['role'],
                'shift' => 'Umum',
                'active' => (bool)($c['active'] ?? true),
                'created' => date('Y-m-d')
            ];
        }, $rawCashiers);

        $totalKasir = count($cashiers);
        $activeKasir = count(array_filter($cashiers, fn($c) => $c['active']));

        require __DIR__ . '/../../views/cashiers.php';
    }

    public function create(): void
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $username === '' || strlen($password) < 6) {
            Session::setFlash('error', 'Nama, username (3-20 karakter), dan password (min 6 karakter) wajib diisi.');
            header('Location: ?page=cashiers');
            exit;
        }

        $resp = $this->api->post('/employees', [
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'role' => 'CASHIER'
        ]);

        if ($resp->isSuccess()) {
            Session::setFlash('success', "Akun kasir '{$name}' (@{$username}) berhasil didaftarkan dan siap login.");
        } else {
            Session::setFlash('error', 'Gagal menambah kasir: ' . ($resp->getErrorMessage() ?? 'Username mungkin sudah ada.'));
        }

        header('Location: ?page=cashiers');
        exit;
    }

    public function toggle(): void
    {
        $id = trim((string)($_POST['id'] ?? ''));
        if ($id === '') {
            header('Location: ?page=cashiers');
            exit;
        }

        $resp = $this->api->post("/employees/{$id}/toggle", []);
        if ($resp->isSuccess()) {
            Session::setFlash('success', 'Status akun kasir berhasil diperbarui.');
        } else {
            Session::setFlash('error', 'Gagal memperbarui status: ' . ($resp->getErrorMessage() ?? 'Terjadi kesalahan.'));
        }

        header('Location: ?page=cashiers');
        exit;
    }

    public function delete(): void
    {
        $id = trim((string)($_POST['id'] ?? ''));
        if ($id === '') {
            header('Location: ?page=cashiers');
            exit;
        }

        $resp = $this->api->delete("/employees/{$id}");
        if ($resp->isSuccess()) {
            Session::setFlash('success', 'Akun kasir berhasil dihapus.');
        } else {
            Session::setFlash('error', 'Gagal menghapus kasir: ' . ($resp->getErrorMessage() ?? 'Terjadi kesalahan.'));
        }

        header('Location: ?page=cashiers');
        exit;
    }
}

