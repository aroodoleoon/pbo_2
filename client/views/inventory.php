<?php
declare(strict_types=1);

$pageTitle = 'Katalog Barang';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Product[] $products */
/** @var array $categories */
/** @var int $totalPhysicalStock */
/** @var int $totalAssetValue */
/** @var int $lowStockCount */
/** @var int $oosCount */

$panel = false;
require __DIR__ . '/partials/catalog.php';
$back = '?page=inventory';
require __DIR__ . '/partials/catalog_dialogs.php';
require_once __DIR__ . '/layout/foot.php';
