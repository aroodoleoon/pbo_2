<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$currentPage = $_GET['page'] ?? 'pos';
$authUser = \PosClient\Session::getAuthUser();
$userName = $authUser['name'] ?? ($authUser['username'] ?? 'Kasir');
$userRole = strtoupper((string)($authUser['role'] ?? 'CASHIER'));
$isManager = ($userRole === 'ADMIN' || $userRole === 'MANAGER');
$userRoleName = $isManager ? 'Manager' : 'Kasir';

$navItems = $isManager ? [
    'reports' => ['Riwayat & Laporan', 'chart'],
    'inventory' => ['Katalog Barang', 'box'],
    'customers' => ['Data Pelanggan', 'users'],
    'cashiers' => ['Kelola Kasir', 'shield'],
] : [
    'pos' => ['POS Kasir', 'receipt'],
    'inventory' => ['Katalog Barang', 'box'],
    'customers' => ['Data Pelanggan', 'users'],
];
?>
<aside id="side">
    <div class="brand">
        <span class="logo"><?= ic('receipt', 22) ?></span>
        <div class="bt">
            <b>POS Academic</b>
            <small><?= $isManager ? 'Ruang manager' : 'Terminal kasir' ?></small>
        </div>
    </div>
    <nav>
        <?php foreach ($navItems as $p => $info): 
            $isOn = ($currentPage === $p);
        ?>
            <a href="?page=<?= e($p) ?>" title="<?= e($info[0]) ?>" class="<?= $isOn ? 'on' : '' ?>">
                <?= ic($info[1], 18) ?>
                <span><?= e($info[0]) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="me">
        <div class="av"><?= e(strtoupper(substr($userName, 0, 1))) ?></div>
        <div class="mi">
            <b><?= e($userName) ?></b>
            <small><?= e($userRoleName) ?></small>
        </div>
        <a class="out" href="?page=logout" title="Keluar">
            <?= ic('logout', 16) ?>
            <span>Keluar</span>
        </a>
    </div>
</aside>
