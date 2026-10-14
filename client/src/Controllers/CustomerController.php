<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;
use PosClient\Models\Customer;

class CustomerController
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

        $query = trim((string)($_GET['query'] ?? ''));
        $params = $query !== '' ? ['query' => $query] : [];

        $resp = $this->api->get('/customers', $params);
        $rawCustomers = $resp->isSuccess() ? ($resp->getData() ?? []) : [];
        $customers = array_map(fn($c) => new Customer($c), $rawCustomers);

        $totalCustomers = count($customers);
        $totalMembers = 0;
        $totalPoints = 0;

        foreach ($customers as $c) {
            if ($c->isMember()) {
                $totalMembers++;
            }
            $totalPoints += $c->getPoints();
        }

        $user = Session::getAuthUser();

        require __DIR__ . '/../../views/customers.php';
    }

    public function create(): void
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $contact = trim((string)($_POST['contact'] ?? ''));
        $asMember = isset($_POST['asMember']);

        if ($name === '' || $contact === '') {
            Session::setFlash('error', 'Nama dan kontak pelanggan wajib diisi.');
            header('Location: ?page=customers');
            exit;
        }

        $resp = $this->api->post('/customers', [
            'name' => $name,
            'contact' => $contact,
            'asMember' => $asMember
        ]);

        if ($resp->isSuccess()) {
            $data = $resp->getData();
            $label = $asMember ? "Member ({$data['memberNumber']})" : "Pelanggan Biasa";
            Session::setFlash('success', "Pelanggan '{$name}' berhasil didaftarkan sebagai {$label}.");
        } else {
            Session::setFlash('error', 'Gagal mendaftarkan pelanggan: ' . ($resp->getErrorMessage() ?? 'Nomor kontak duplikat'));
        }

        header('Location: ?page=customers');
        exit;
    }
}
