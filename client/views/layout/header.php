<?php
declare(strict_types=1);

$flash = \PosClient\Session::getFlash();
$indonesianDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$indonesianMonths = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];
$dayName = $indonesianDays[(int)date('w')];
$monthName = $indonesianMonths[(int)date('n')];
$currentFormattedDate = "{$dayName}, " . date('j') . " {$monthName} " . date('Y');
?>
<div class="pl-64 flex flex-col flex-1 min-h-screen">
    <!-- Top Header Bar -->
    <header class="fixed top-0 left-64 right-0 h-16 bg-surface-container-lowest/90 backdrop-blur-md border-b border-outline-variant/20 z-40 px-gutter-lg flex items-center justify-between">
        <div class="flex items-center gap-space-md">
            <div class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md">
                <span class="w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
                <span class="font-semibold text-secondary">Backend Ktor API: Terhubung</span>
            </div>
            <span class="text-outline-variant">•</span>
            <span class="text-outline text-label-sm">v<?= \PosClient\Config::APP_VERSION ?></span>
        </div>

        <div class="flex items-center gap-space-lg">
            <div class="flex items-center gap-space-sm bg-surface-container-low px-space-md py-space-xs rounded-full border border-outline-variant/30 text-on-surface font-label-md text-label-md">
                <span class="material-symbols-outlined text-[18px] text-primary">calendar_today</span>
                <span><?= $currentFormattedDate ?></span>
            </div>

            <div class="flex items-center gap-space-sm">
                <a href="?page=pos" title="POS Kasir Cepat (F1)" class="p-space-xs text-on-surface-variant hover:text-on-surface rounded-lg hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-[20px]">point_of_sale</span>
                </a>
                <a href="?page=inventory" title="Cek Stok Gudang" class="p-space-xs text-on-surface-variant hover:text-on-surface rounded-lg hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-[20px]">inventory</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Toast -->
    <?php if ($flash): ?>
    <div class="fixed top-20 right-6 z-50 max-w-md animate-in slide-in-from-top-2 duration-200" id="globalToast">
        <div class="p-4 rounded-xl shadow-xl flex items-start gap-3 border <?= $flash['type'] === 'success' ? 'bg-secondary text-on-secondary border-secondary-fixed' : ($flash['type'] === 'error' ? 'bg-error text-on-error border-error-container' : 'bg-inverse-surface text-inverse-on-surface border-outline-variant/30') ?>">
            <span class="material-symbols-outlined text-[22px]">
                <?= $flash['type'] === 'success' ? 'check_circle' : ($flash['type'] === 'error' ? 'error' : 'info') ?>
            </span>
            <div class="flex-1 text-sm font-medium">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <button onclick="document.getElementById('globalToast').remove()" class="opacity-80 hover:opacity-100">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('globalToast');
            if (toast) toast.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="w-full pt-16 flex-1 bg-surface flex flex-col">

