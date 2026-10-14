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

// 2. Initialize Session
Session::start();

// 3. Dependency Injection
$apiClient = new ApiClient(Config::API_BASE_URL);
$authCtrl = new AuthController($apiClient);
$posCtrl = new PosController($apiClient);
$inventoryCtrl = new InventoryController($apiClient);
$reportCtrl = new ReportController($apiClient);
$customerCtrl = new CustomerController($apiClient);

// 4. Request Routing
$page = (string)($_GET['page'] ?? 'pos');

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
    case 'do-create-product':
        $inventoryCtrl->createProduct();
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

    default:
        header('Location: ?page=pos');
        exit;
}
