<?php
declare(strict_types=1);

$pageTitle = 'Laporan Penjualan & Performa Kasir';
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/sidebar.php';
require __DIR__ . '/layout/header.php';

/** @var array $filteredTransactions */
/** @var int $totalTransactions */
/** @var int $totalOmzet */
/** @var int $totalDiskon */
/** @var int $totalCash */
/** @var int $totalNonCash */
/** @var int $avgPerTransaction */
/** @var array $salesSummary */
/** @var string $startDate */
/** @var string $endDate */
/** @var string $cashierFilter */
/** @var string $paymentFilter */
/** @var array $user */
?>
<div class="flex flex-col w-full p-gutter-lg gap-space-xl">
    <!-- TOP HEADER & CONTROLS SECTION -->
    <div class="flex flex-col gap-space-lg">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    <span>Modul Laporan & Transaksi (Josua)</span>
                    <span class="text-outline-variant">•</span>
                    <span>Terminal Akademik POS</span>
                </div>
                <h1 class="font-headline-xl text-headline-xl text-on-surface font-bold tracking-tight">Laporan Penjualan & Performa Kasir</h1>
            </div>
            
            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-space-sm flex-wrap">
                <a href="?page=export-csv" class="flex items-center gap-space-xs px-space-md py-space-sm rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md shadow-sm transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px] text-primary">download</span>
                    <span>Ekspor CSV</span>
                </a>
                <button type="button" onclick="window.print()" class="flex items-center gap-space-xs px-space-md py-space-sm rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md shadow-sm transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px] text-error">picture_as_pdf</span>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <!-- FILTER BAR PANEL -->
        <form method="GET" action="" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
            <input type="hidden" name="page" value="reports"/>
            
            <!-- Date pickers & quick chips -->
            <div class="flex flex-wrap items-center gap-space-sm">
                <div class="flex items-center gap-space-xs bg-surface-container-low px-space-md py-2 rounded-lg">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Dari</span>
                    <div class="flex items-center gap-1 text-on-surface font-body-md text-body-md font-semibold">
                        <span class="material-symbols-outlined text-[16px] text-outline">calendar_today</span>
                        <input type="date" name="from" value="<?= htmlspecialchars($startDate) ?>" class="bg-transparent border-0 outline-none text-on-surface text-body-md cursor-pointer"/>
                    </div>
                </div>
                <div class="flex items-center gap-space-xs bg-surface-container-low px-space-md py-2 rounded-lg">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Sampai</span>
                    <div class="flex items-center gap-1 text-on-surface font-body-md text-body-md font-semibold">
                        <span class="material-symbols-outlined text-[16px] text-outline">event</span>
                        <input type="date" name="to" value="<?= htmlspecialchars($endDate) ?>" class="bg-transparent border-0 outline-none text-on-surface text-body-md cursor-pointer"/>
                    </div>
                </div>
            </div>

            <!-- Selectors & Apply Button -->
            <div class="flex flex-wrap items-center gap-space-sm">
                <!-- Payment Method Select -->
                <div class="relative min-w-[160px]">
                    <select name="method" class="w-full bg-surface-container-low text-on-surface font-label-md text-label-md px-space-md py-2.5 rounded-lg outline-none cursor-pointer">
                        <option value="all" <?= $paymentFilter === 'all' ? 'selected' : '' ?>>Semua Metode</option>
                        <option value="CASH" <?= $paymentFilter === 'CASH' ? 'selected' : '' ?>>TUNAI (Cash)</option>
                        <option value="QRIS" <?= $paymentFilter === 'QRIS' ? 'selected' : '' ?>>QRIS Dinamis</option>
                        <option value="DEBIT" <?= $paymentFilter === 'DEBIT' ? 'selected' : '' ?>>DEBIT Card</option>
                    </select>
                </div>
                <button type="submit" class="flex items-center gap-space-xs px-space-lg py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md shadow-md hover:bg-primary-container active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 4 METRIC SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-lg">
        <!-- Card 1: Total Transaksi -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex flex-col justify-between gap-space-md relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface-variant font-medium">Total Transaksi</span>
                <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">shopping_bag</span>
                </div>
            </div>
            <div>
                <div class="font-numeric-total text-numeric-total text-on-surface leading-tight tracking-tight font-extrabold">
                    <?= $totalTransactions ?> <span class="text-body-lg font-normal text-on-surface-variant">Trx</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center text-secondary font-label-md text-label-md bg-secondary-fixed/30 px-1.5 py-0.5 rounded font-semibold">
                        <?= $totalCash > 0 ? 'Tunai: Rp ' . number_format($totalCash, 0, ',', '.') : 'Tervalidasi' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Omzet -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex flex-col justify-between gap-space-md relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface-variant font-medium">Total Penjualan Bersih</span>
                <div class="w-10 h-10 rounded-lg bg-secondary-container/40 flex items-center justify-center text-secondary group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">payments</span>
                </div>
            </div>
            <div>
                <div class="font-numeric-total text-numeric-total text-on-surface leading-tight tracking-tight font-extrabold">
                    Rp <?= number_format($totalOmzet, 0, ',', '.') ?>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-secondary font-label-md font-semibold">
                    <span>Non-Tunai: Rp <?= number_format($totalNonCash, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Diskon Member -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex flex-col justify-between gap-space-md relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface-variant font-medium">Potongan Diskon Member</span>
                <div class="w-10 h-10 rounded-lg bg-tertiary-fixed/30 flex items-center justify-center text-tertiary group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">loyalty</span>
                </div>
            </div>
            <div>
                <div class="font-numeric-total text-numeric-total text-on-surface leading-tight tracking-tight font-extrabold">
                    Rp <?= number_format($totalDiskon, 0, ',', '.') ?>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-outline font-label-md">
                    <span>Hemat oleh pelanggan</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Rata-Rata Belanja -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex flex-col justify-between gap-space-md relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface-variant font-medium">Rata-Rata Basket Size</span>
                <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">receipt</span>
                </div>
            </div>
            <div>
                <div class="font-numeric-total text-numeric-total text-on-surface leading-tight tracking-tight font-extrabold">
                    Rp <?= number_format($avgPerTransaction, 0, ',', '.') ?>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-outline font-label-md">
                    <span>Per struk penjualan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TRANSACTIONS HISTORY TABLE -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden flex flex-col gap-3 p-space-lg">
        <div class="flex items-center justify-between">
            <h3 class="font-headline-md text-headline-md text-on-surface font-bold">Riwayat Transaksi Penjualan</h3>
            <span class="text-xs text-outline">Menampilkan <?= count($filteredTransactions) ?> transaksi terakhir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/20 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Tanggal & Jam</th>
                        <th class="py-3 px-4">Kasir ID</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Metode</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-right">Diskon</th>
                        <th class="py-3 px-4 text-right">Total Akhir</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-sm">
                    <?php if (empty($filteredTransactions)): ?>
                    <tr>
                        <td colspan="9" class="py-12 text-center text-outline">
                            Belum ada riwayat transaksi yang cocok dengan filter yang dipilih.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($filteredTransactions as $trx): ?>
                    <tr class="hover:bg-surface-container-low/40 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-primary">
                            <?= htmlspecialchars($trx['transactionId']) ?>
                        </td>
                        <td class="py-3 px-4 text-on-surface-variant text-xs">
                            <?= htmlspecialchars($trx['transactionDate'] ?? date('Y-m-d H:i')) ?>
                        </td>
                        <td class="py-3 px-4 text-xs font-mono text-outline">
                            <?= htmlspecialchars(substr($trx['cashierId'] ?? 'CASHIER', 0, 8)) ?>...
                        </td>
                        <td class="py-3 px-4 text-xs">
                            <?= !empty($trx['customerId']) ? '<span class="font-bold text-secondary">Member</span>' : '<span class="text-outline">Umum</span>' ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <?php $m = strtoupper($trx['paymentMethod'] ?? 'CASH'); ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $m === 'CASH' ? 'bg-secondary-container text-on-secondary-container' : ($m === 'QRIS' ? 'bg-primary-container text-on-primary' : 'bg-surface-container-high text-on-surface') ?>">
                                <?= $m ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-on-surface-variant">
                            Rp <?= number_format((int)($trx['subtotal'] ?? $trx['total']), 0, ',', '.') ?>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-error">
                            <?= (int)($trx['discountAmount'] ?? 0) > 0 ? '-Rp ' . number_format((int)$trx['discountAmount'], 0, ',', '.') : '-' ?>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-on-surface">
                            Rp <?= number_format((int)($trx['total'] ?? 0), 0, ',', '.') ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-secondary">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                <span><?= htmlspecialchars($trx['paymentStatus'] ?? 'SUCCESS') ?></span>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layout/foot.php'; ?>
