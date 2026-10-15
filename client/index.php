<?php
declare(strict_types=1);

/**
 * POS Academic Retail Terminal — Client Web Front Controller
 * PHP OOP Client Architecture connecting to Ktor Backend REST API
 */

// 1. SPL Class Autoloader
spl_autoload_register(function (string $class) {
    $prefix = 'PosClient\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use PosClient\Config;
use PosClient\Session;
use PosClient\ApiClient;
use PosClient\Controllers\AuthController;
use PosClient\Controllers\PosController;
use PosClient\Controllers\InventoryController;
use PosClient\Controllers\ReportController;
use PosClient\Controllers\CustomerController;
use PosClient\Controllers\CashierController;

// 2. Initialize Session
date_default_timezone_set('Asia/Jakarta');
Session::start();

// 3. Dependency Injection
$apiClient = new ApiClient(Config::getApiBaseUrl());
$authCtrl = new AuthController($apiClient);
$posCtrl = new PosController($apiClient);
$inventoryCtrl = new InventoryController($apiClient);
$reportCtrl = new ReportController($apiClient);
$customerCtrl = new CustomerController($apiClient);
$cashierCtrl = new CashierController($apiClient);

// 4. Request Routing + otorisasi peran (ditegakkan di server)
$isManager = \PosClient\Http::isManager();
$page = (string)($_GET['page'] ?? ($isManager ? 'reports' : 'pos'));

$publicPages = ['login', 'do-login', 'logout'];
$managerOnly = ['reports', 'export-csv', 'cashiers', 'do-create-cashier', 'do-toggle-cashier', 'do-delete-cashier'];
$cashierOnly = ['pos', 'api-search', 'api-barcode-scan', 'api-add-cart', 'api-update-cart', 'api-remove-cart', 'api-clear-cart', 'api-select-customer', 'api-checkout'];

if (!in_array($page, $publicPages, true)) {
    if (!Session::isLoggedIn()) {
        if (str_starts_with($page, 'api-')) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'ok' => false, 'message' => 'Sesi berakhir, silakan login ulang.']);
            exit;
        }
        header('Location: ?page=login');
        exit;
    }
    if ($isManager && in_array($page, $cashierOnly, true)) {
        Session::setFlash('error', 'Terminal kasir hanya untuk akun Kasir.');
        header('Location: ?page=reports');
        exit;
    }
    if (!$isManager && in_array($page, $managerOnly, true)) {
        Session::setFlash('error', 'Akses ditolak: halaman ini hanya untuk Manager.');
        header('Location: ?page=pos');
        exit;
    }
    // Kasir membuka Katalog & Pelanggan sebagai panel di halaman POS (keranjang tetap ada)
    if (!$isManager && $page === 'inventory') {
        header('Location: ?page=pos#katalog');
        exit;
    }
    if (!$isManager && $page === 'customers') {
        header('Location: ?page=pos#member');
        exit;
    }
}

switch ($page) {
    // Auth Routes
    case 'login':
        $authCtrl->showLogin();
        break;
    case 'do-login':
        $authCtrl->login();
        break;
    case 'logout':
        $authCtrl->logout();
        break;

    // POS Kasir Routes
    case 'pos':
        $posCtrl->index();
        break;
    case 'api-search':
        $posCtrl->apiSearch();
        break;
    case 'api-barcode-scan':
        $posCtrl->apiBarcodeScan();
        break;
    case 'api-add-cart':
        $posCtrl->apiAddToCart();
        break;
    case 'api-update-cart':
        $posCtrl->apiUpdateCart();
        break;
    case 'api-remove-cart':
        $posCtrl->apiRemoveCart();
        break;
    case 'api-clear-cart':
        $posCtrl->apiClearCart();
        break;
    case 'api-select-customer':
        $posCtrl->apiSelectCustomer();
        break;
    case 'api-checkout':
        $posCtrl->apiCheckout();
        break;

    // Inventory & Warehouse Routes
    case 'inventory':
        $inventoryCtrl->index();
        break;
    case 'do-inventory-receipt':
        $inventoryCtrl->receipt();
        break;
    case 'do-inventory-adjust':
        $inventoryCtrl->adjust();
        break;
    case 'do-update-stock':
        $inventoryCtrl->updateStock();
        break;
    case 'do-create-product':
        $inventoryCtrl->createProduct();
        break;
    case 'do-create-category':
        $inventoryCtrl->createCategory();
        break;
    case 'api-create-category':
        $inventoryCtrl->apiCreateCategory();
        break;

    // Reports & Analytics Routes
    case 'reports':
        $reportCtrl->index();
        break;
    case 'export-csv':
        $reportCtrl->exportCsv();
        break;

    // Customers & Members Routes
    case 'customers':
        $customerCtrl->index();
        break;
    case 'do-create-customer':
        $customerCtrl->create();
        break;

    // Cashier Management Routes (Admin only)
    case 'cashiers':
        $cashierCtrl->index();
        break;
    case 'do-create-cashier':
        $cashierCtrl->create();
        break;
    case 'do-toggle-cashier':
        $cashierCtrl->toggle();
        break;
    case 'do-delete-cashier':
        $cashierCtrl->delete();
        break;

    default:
        header('Location: ?page=pos');
        exit;
}

