<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;
use PosClient\Http;
use PosClient\Models\Product;

class InventoryController
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

        // Ambil semua produk
        $prodResp = $this->api->get('/products');
        $rawProducts = $prodResp->isSuccess() ? ($prodResp->getData() ?? []) : [];
        $products = array_map(fn($p) => new Product($p), $rawProducts);

        // Ambil barang stok menipis
        $lowStockResp = $this->api->get('/inventory/low-stock');
        $lowStockList = $lowStockResp->isSuccess() ? ($lowStockResp->getData() ?? []) : [];

        // Ambil riwayat pergerakan stok
        $movResp = $this->api->get('/inventory/movements');
        $movements = $movResp->isSuccess() ? ($movResp->getData() ?? []) : [];

        // Ambil kategori
        $catResp = $this->api->get('/categories');
        $categories = $catResp->isSuccess() ? ($catResp->getData() ?? []) : [];

        // Hitung KPIs
        $totalSku = count($products);
        $totalPhysicalStock = 0;
        $totalAssetValue = 0;
        $oosCount = 0;
        $lowStockCount = 0;

        foreach ($products as $p) {
            $totalPhysicalStock += $p->getStock();
            $totalAssetValue += ($p->getStock() * $p->getBuyPrice());
            if ($p->isOutOfStock()) {
                $oosCount++;
            } elseif ($p->isLowStock()) {
                $lowStockCount++;
            }
        }

        $user = Session::getAuthUser();

        require __DIR__ . '/../../views/inventory.php';
    }

    public function receipt(): void
    {
        $productId = trim((string)($_POST['productId'] ?? ''));
        $quantity = (int)($_POST['quantity'] ?? 0);
        $source = trim((string)($_POST['source'] ?? 'Supplier'));
        $notes = trim((string)($_POST['notes'] ?? 'Penerimaan barang masuk'));

        if ($productId === '' || $quantity <= 0) {
            Session::setFlash('error', 'Produk dan kuantitas barang masuk harus valid.');
            Http::back('?page=inventory');
        }

        $resp = $this->api->post('/inventory/receipts', [
            'productId' => $productId,
            'quantity' => $quantity,
            'source' => $source,
            'notes' => $notes
        ]);

        if ($resp->isSuccess()) {
            Session::setFlash('success', "Berhasil menerima {$quantity} pcs stok barang masuk.");
        } else {
            Session::setFlash('error', 'Gagal restok: ' . ($resp->getErrorMessage() ?? 'Kesalahan server'));
        }

        Http::back('?page=inventory');
    }

    public function adjust(): void
    {
        $productId = trim((string)($_POST['productId'] ?? ''));
        $quantity = (int)($_POST['quantity'] ?? 0);
        $type = trim((string)($_POST['type'] ?? 'OUT_DAMAGED'));
        $reason = trim((string)($_POST['reason'] ?? 'Penyesuaian stok fisik'));

        if ($productId === '' || $quantity <= 0) {
            Session::setFlash('error', 'Produk dan kuantitas penyesuaian harus valid.');
            Http::back('?page=inventory');
        }

        $resp = $this->api->post('/inventory/adjustments', [
            'productId' => $productId,
            'quantity' => $quantity,
            'type' => $type,
            'reason' => $reason
        ]);

        if ($resp->isSuccess()) {
            Session::setFlash('success', "Penyesuaian stok sebanyak {$quantity} pcs berhasil dicatat.");
        } else {
            Session::setFlash('error', 'Gagal menyesuaikan stok: ' . ($resp->getErrorMessage() ?? 'Kesalahan server'));
        }

        Http::back('?page=inventory');
    }

    public function updateStock(): void
    {
        $productId = trim((string)($_POST['id'] ?? ($_POST['productId'] ?? '')));
        $qty = (int)($_POST['qty'] ?? ($_POST['quantity'] ?? 0));
        $ket = mb_substr(trim((string)($_POST['ket'] ?? '')), 0, 150);

        if ($productId === '' || $qty === 0) {
            Session::setFlash('error', 'Jumlah perubahan stok tidak boleh 0.');
            Http::back('?page=inventory');
        }

        // Kasir wajib mengisi keterangan untuk setiap aksi gudang (divalidasi di server)
        if (Http::isCashier() && $ket === '') {
            Session::setFlash('error', 'Keterangan wajib diisi untuk setiap perubahan stok.');
            Http::back('?page=inventory');
        }

        if ($qty > 0) {
            $resp = $this->api->post('/inventory/receipts', [
                'productId' => $productId,
                'quantity' => $qty,
                'source' => 'Penyesuaian dari POS',
                'notes' => $ket !== '' ? $ket : 'Tambah stok via katalog'
            ]);
        } else {
            $resp = $this->api->post('/inventory/adjustments', [
                'productId' => $productId,
                'quantity' => -$qty,
                'type' => 'OUT_DAMAGED',
                'reason' => $ket !== '' ? $ket : 'Koreksi pengurangan stok dari POS'
            ]);
        }

        if ($resp->isSuccess()) {
            Session::setFlash('success', 'Stok berhasil diperbarui.');
        } else {
            Session::setFlash('error', 'Gagal memperbarui stok: ' . ($resp->getErrorMessage() ?? 'Kesalahan server'));
        }

        Http::back('?page=inventory');
    }

    public function createProduct(): void
    {
        $barcode = trim((string)($_POST['sku'] ?? ($_POST['barcode'] ?? '')));
        $name = trim((string)($_POST['name'] ?? ''));
        $categoryId = trim((string)($_POST['cat'] ?? ($_POST['categoryId'] ?? '')));
        $buyPrice = (int)($_POST['buy'] ?? ($_POST['buyPrice'] ?? 0));
        $sellPrice = (int)($_POST['sell'] ?? ($_POST['sellPrice'] ?? 0));
        $initialStock = (int)($_POST['stock'] ?? ($_POST['initialStock'] ?? 0));
        $minStock = (int)($_POST['min'] ?? ($_POST['minStock'] ?? 5));

        if ($barcode === '' || $name === '' || $sellPrice <= 0) {
            Session::setFlash('error', 'Nama barang, barcode, dan harga jual wajib diisi.');
            Http::back('?page=inventory');
        }

        $resp = $this->api->post('/products', [
            'barcode' => $barcode,
            'name' => $name,
            'categoryId' => $categoryId !== '' ? $categoryId : null,
            'buyPrice' => $buyPrice,
            'sellPrice' => $sellPrice,
            'initialStock' => $initialStock,
            'minStock' => $minStock,
        ]);

        if ($resp->isSuccess()) {
            Session::setFlash('success', "Produk baru '{$name}' berhasil didaftarkan ke katalog.");
        } else {
            Session::setFlash('error', 'Gagal menambah produk: ' . ($resp->getErrorMessage() ?? 'Katalog duplicate atau validasi gagal'));
        }

        Http::back('?page=inventory');
    }

    public function createCategory(): void
    {
        $name = trim((string)($_POST['name'] ?? ''));

        if ($name === '') {
            Session::setFlash('error', 'Nama kategori wajib diisi.');
            Http::back('?page=inventory');
        }

        $resp = $this->api->post('/categories', [
            'name' => $name
        ]);

        if ($resp->isSuccess()) {
            Session::setFlash('success', "Kategori baru '{$name}' berhasil ditambahkan.");
        } else {
            Session::setFlash('error', 'Gagal menambah kategori: ' . ($resp->getErrorMessage() ?? 'Kategori sudah ada atau terjadi kesalahan.'));
        }

        Http::back('?page=inventory');
    }

    public function apiCreateCategory(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim((string)($input['name'] ?? ''));

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi.']);
            exit;
        }

        $resp = $this->api->post('/categories', [
            'name' => $name
        ]);

        if ($resp->isSuccess()) {
            echo json_encode([
                'success' => true,
                'data' => $resp->getData(),
                'message' => "Kategori '{$name}' berhasil ditambahkan"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $resp->getErrorMessage() ?? 'Gagal membuat kategori'
            ]);
        }
        exit;
    }
}

