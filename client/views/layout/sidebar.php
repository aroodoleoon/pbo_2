<?php
declare(strict_types=1);

$currentPage = $_GET['page'] ?? 'pos';
$authUser = \PosClient\Session::getAuthUser();
$userName = $authUser['name'] ?? 'Kasir';
$userRole = $authUser['role'] ?? 'CASHIER';
?>
<aside class="fixed left-0 top-0 h-screen w-64 bg-inverse-surface border-r border-outline-variant/20 z-50 flex flex-col justify-between select-none">
    <div class="flex flex-col">
        <!-- Logo -->
        <div class="h-16 px-gutter flex items-center gap-space-sm border-b border-outline-variant/10">
            <div class="w-9 h-9 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-bold shadow-md">
                <span class="material-symbols-outlined text-[22px]">point_of_sale</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-md text-headline-md text-inverse-on-surface leading-tight font-bold">POS Academic</span>
                <span class="font-label-sm text-label-sm text-outline-variant uppercase tracking-wider">Retail Terminal</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex flex-col gap-space-xs px-space-sm pt-space-lg">
            <a href="?page=pos" 
               class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg transition-colors <?= $currentPage === 'pos' ? 'bg-primary-container text-on-primary font-bold shadow-sm' : 'text-inverse-on-surface/80 hover:bg-surface-container-high/10 hover:text-inverse-on-surface' ?>">
                <span class="material-symbols-outlined text-[20px]">point_of_sale</span>
                <span>POS Kasir</span>
            </a>

            <a href="?page=inventory" 
               class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg transition-colors <?= $currentPage === 'inventory' ? 'bg-primary-container text-on-primary font-bold shadow-sm' : 'text-inverse-on-surface/80 hover:bg-surface-container-high/10 hover:text-inverse-on-surface' ?>">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                <span>Katalog Barang</span>
            </a>

            <a href="?page=reports" 
               class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg transition-colors <?= $currentPage === 'reports' ? 'bg-primary-container text-on-primary font-bold shadow-sm' : 'text-inverse-on-surface/80 hover:bg-surface-container-high/10 hover:text-inverse-on-surface' ?>">
                <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                <span>Riwayat & Laporan</span>
            </a>

            <a href="?page=customers" 
               class="flex items-center gap-space-md px-space-md py-space-sm rounded-lg transition-colors <?= $currentPage === 'customers' ? 'bg-primary-container text-on-primary font-bold shadow-sm' : 'text-inverse-on-surface/80 hover:bg-surface-container-high/10 hover:text-inverse-on-surface' ?>">
                <span class="material-symbols-outlined text-[20px]">group</span>
                <span>Data Pelanggan</span>
            </a>
        </nav>
    </div>

    <!-- User Profile & Logout -->
    <div class="p-space-md border-t border-outline-variant/10 flex flex-col gap-space-md bg-inverse-surface/80">
        <div class="flex items-center gap-space-md p-space-sm rounded-lg bg-surface-container-low/5">
            <div class="relative flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-bold text-sm">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </div>
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-secondary-fixed rounded-full ring-2 ring-inverse-surface"></span>
            </div>
            <div class="flex flex-col min-w-0 flex-1">
                <span class="font-body-md text-body-md font-semibold text-inverse-on-surface truncate"><?= htmlspecialchars($userName) ?></span>
                <span class="font-label-sm text-label-sm text-outline-variant truncate"><?= htmlspecialchars($userRole) ?> • Terminal A</span>
            </div>
        </div>
        <a href="?page=logout" class="flex items-center justify-center gap-space-sm w-full py-space-sm px-space-md rounded-lg text-error hover:bg-error-container/20 transition-colors font-body-md text-body-md">
            <span class="material-symbols-outlined text-[18px]">logout</span>
            <span>Logout</span>
        </a>
    </div>
</aside>

