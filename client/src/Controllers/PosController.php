<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;
use PosClient\Models\Product;
use PosClient\Models\Customer;
use PosClient\Models\Cart;

class PosController
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

        // Ambil produk dari backend
        $productsResponse = $this->api->get('/products');
        $rawProducts = $productsResponse->isSuccess() ? ($productsResponse->getData() ?? []) : [];
        $products = array_map(fn($p) => new Product($p), $rawProducts);

        // Ambil kategori
        $categoriesResponse = $this->api->get('/categories');
        $categories = $categoriesResponse->isSuccess() ? ($categoriesResponse->getData() ?? []) : [];

        // Ambil daftar pelanggan/member
        $customersResponse = $this->api->get('/customers');
        $rawCustomers = $customersResponse->isSuccess() ? ($customersResponse->getData() ?? []) : [];
        $customers = array_map(fn($c) => new Customer($c), $rawCustomers);

        $cartSummary = Cart::calculateSummary();
        $user = Session::getAuthUser();

        require __DIR__ . '/../../views/pos.php';
    }

    public function apiSearch(): void
    {
        header('Content-Type: application/json');
        $q = trim((string)($_GET['q'] ?? ''));
        $cat = trim((string)($_GET['category'] ?? ''));

        $params = [];
        if ($q !== '') $params['query'] = $q;
        if ($cat !== '' && $cat !== 'all') $params['categoryId'] = $cat;

        $resp = $this->api->get('/products', $params);
        if ($resp->isSuccess()) {
            $items = array_map(fn($p) => (new Product($p))->toArray(), $resp->getData() ?? []);
            echo json_encode(['success' => true, 'data' => $items]);
        } else {
            echo json_encode(['success' => false, 'message' => $resp->getErrorMessage()]);
        }
        exit;
    }

    public function apiAddToCart(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (string)($input['productId'] ?? '');
        $qty = max(1, (int)($input['quantity'] ?? 1));

        if ($productId === '') {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $prodResp = $this->api->get('/products/' . urlencode($productId));
        if (!$prodResp->isSuccess() || empty($prodResp->getData())) {
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan di server']);
            exit;
        }

        $product = new Product($prodResp->getData());
        Cart::addItem($product, $qty);

        echo json_encode([
            'success' => true,
            'cart' => Cart::calculateSummary()
        ]);
        exit;
    }

    public function apiUpdateCart(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (string)($input['productId'] ?? '');
        $qty = (int)($input['quantity'] ?? 1);

        Cart::updateQuantity($productId, $qty);

        echo json_encode([
            'success' => true,
            'cart' => Cart::calculateSummary()
        ]);
        exit;
    }

    public function apiRemoveCart(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (string)($input['productId'] ?? '');

        Cart::removeItem($productId);

        echo json_encode([
            'success' => true,
            'cart' => Cart::calculateSummary()
        ]);
        exit;
    }

    public function apiClearCart(): void
    {
        header('Content-Type: application/json');
        Cart::clear();
        echo json_encode([
            'success' => true,
            'cart' => Cart::calculateSummary()
        ]);
        exit;
    }

    public function apiSelectCustomer(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $customerId = (string)($input['customerId'] ?? '');

        if ($customerId === '' || $customerId === 'none') {
            Cart::setCustomer(null);
        } else {
            $resp = $this->api->get('/customers/' . urlencode($customerId));
            if ($resp->isSuccess() && $resp->getData()) {
                Cart::setCustomer($resp->getData());
            } else {
                echo json_encode(['success' => false, 'message' => 'Pelanggan tidak ditemukan']);
                exit;
            }
        }

        echo json_encode([
            'success' => true,
            'cart' => Cart::calculateSummary()
        ]);
        exit;
    }

    public function apiCheckout(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $method = strtoupper((string)($input['method'] ?? 'CASH'));
        $amountPaid = (int)($input['amountPaid'] ?? 0);

        $cart = Cart::calculateSummary();
        $customerId = $input['customerId'] ?? ($input['cust'] ?? ($cart['customer']['id'] ?? null));
        if ($customerId === '0' || $customerId === '' || $customerId === 'none') {
            $customerId = null;
        }

        $items = [];
        if (!empty($input['items'])) {
            foreach ($input['items'] as $it) {
                $pid = (string)($it['productId'] ?? ($it['id'] ?? ''));
                $qty = (int)($it['quantity'] ?? ($it['qty'] ?? 1));
                if ($pid !== '' && $qty > 0) {
                    $items[] = ['productId' => $pid, 'quantity' => $qty];
                }
            }
        } elseif (!empty($cart['items'])) {
            $items = array_map(fn($item) => [
                'productId' => $item['id'],
                'quantity' => $item['quantity'],
            ], $cart['items']);
        }

        if (empty($items)) {
            echo json_encode(['success' => false, 'ok' => false, 'message' => 'Keranjang belanja masih kosong!']);
            exit;
        }

        // 1. Buat keranjang transaksi di backend
        $salePayload = [
            'customerId' => $customerId,
            'items' => $items,
            'discountAmount' => (int)($input['discountAmount'] ?? ($cart['discountAmount'] ?? 0)),
        ];

        $saleResp = $this->api->post('/sales', $salePayload);
        if (!$saleResp->isSuccess()) {
            echo json_encode([
                'success' => false,
                'ok' => false,
                'message' => 'Gagal membuat penjualan: ' . ($saleResp->getErrorMessage() ?? 'Stok tidak mencukupi'),
                'msg' => $saleResp->getErrorMessage() ?? 'Stok tidak mencukupi'
            ]);
            exit;
        }

        $saleData = $saleResp->getData();
        $saleId = $saleData['id'] ?? null;
        if (!$saleId) {
            echo json_encode(['success' => false, 'ok' => false, 'message' => 'Backend tidak mengembalikan ID transaksi', 'msg' => 'Backend tidak mengembalikan ID transaksi']);
            exit;
        }

        // 2. Eksekusi Pembayaran di backend
        $totalTagihan = (int)($saleData['total'] ?? ($cart['total'] ?? 0));
        if ($method === 'CASH' && $amountPaid < $totalTagihan) {
            echo json_encode([
                'success' => false,
                'ok' => false,
                'message' => "Uang tunai kurang! Tagihan: Rp " . number_format($totalTagihan, 0, ',', '.') . ", Diberikan: Rp " . number_format($amountPaid, 0, ',', '.'),
                'msg' => "Uang tunai kurang! Tagihan: Rp " . number_format($totalTagihan, 0, ',', '.')
            ]);
            exit;
        }

        $paymentPayload = [
            'method' => $method,
            'amountPaid' => ($method === 'CASH') ? $amountPaid : $totalTagihan,
            'items' => $items,
            'discountAmount' => (int)($saleData['discountAmount'] ?? ($cart['discountAmount'] ?? 0)),
            'taxAmount' => (int)($saleData['taxAmount'] ?? ($cart['taxAmount'] ?? 0)),
            'customerId' => $customerId,
        ];

        $payResp = $this->api->post("/sales/{$saleId}/payments", $paymentPayload);
        if (!$payResp->isSuccess()) {
            echo json_encode([
                'success' => false,
                'ok' => false,
                'message' => 'Pembayaran gagal: ' . ($payResp->getErrorMessage() ?? 'Gagal memproses metode ' . $method),
                'msg' => 'Pembayaran gagal: ' . ($payResp->getErrorMessage() ?? 'Gagal memproses metode ' . $method)
            ]);
            exit;
        }

        $paymentData = $payResp->getData();

        // 3. Clear cart setelah pembayaran berhasil
        Cart::clear();

        echo json_encode([
            'success' => true,
            'ok' => true,
            'sale' => $saleData,
            'payment' => $paymentData,
            'total' => $paymentData['total'] ?? $totalTagihan,
            'change' => $paymentData['changeAmount'] ?? max(0, $amountPaid - $totalTagihan),
            'message' => 'Transaksi berhasil diselesaikan!'
        ]);
        exit;
    }
}

