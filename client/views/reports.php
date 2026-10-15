<?php
declare(strict_types=1);

$pageTitle = 'Laporan Penjualan & Performa Kasir';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var array $filteredTransactions */
/** @var int $totalTransactions */
/** @var int $totalOmzet */
/** @var int $totalDiskon */
/** @var int $totalCash */
/** @var int $totalNonCash */
/** @var int $avgPerTransaction */
/** @var string $startDate */
/** @var string $endDate */
/** @var string $cashierFilter */
/** @var string $paymentFilter */
/** @var array $user */

$n = count($filteredTransactions);
$avgBasket = $n > 0 ? (int)round($totalOmzet / $n) : 0;
?>
<div class="bar">
    <p class="mut">Modul laporan & transaksi · Terminal akademik POS</p>
    <div class="acts">
        <a class="btn ghost" href="?<?= e(http_build_query(['page' => 'export-csv', 'from' => $startDate, 'to' => $endDate, 'method' => $paymentFilter, 'cashier' => $cashierFilter])) ?>">
            <?= ic('download', 16) ?> Ekspor CSV
        </a>
        <button type="button" class="btn ghost" onclick="window.print()">
            <?= ic('printer', 16) ?> Cetak laporan
        </button>
    </div>
</div>

<form class="card filt" method="get" action="">
    <input type="hidden" name="page" value="reports">
    <label class="f">
        <span class="lb"><?= ic('calendar', 15) ?> Dari</span>
        <input type="date" name="from" value="<?= e($startDate) ?>">
    </label>
    <label class="f">
        <span class="lb"><?= ic('calendar', 15) ?> Sampai</span>
        <input type="date" name="to" value="<?= e($endDate) ?>">
    </label>
    <label class="f">
        <span class="lb"><?= ic('wallet', 15) ?> Metode</span>
        <select name="method">
            <option value="all">Semua metode</option>
            <?php foreach (['CASH' => 'Tunai', 'QRIS' => 'QRIS', 'DEBIT' => 'Debit', 'TRANSFER' => 'Transfer'] as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= strtoupper($paymentFilter) === $k ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn" type="submit"><?= ic('filter', 16) ?> Terapkan filter</button>
</form>

<div class="stats">
    <?= statcard('Total transaksi', $n . ' trx', 'Tervalidasi di backend', 'bag') ?>
    <?= statcard('Total penjualan bersih', rp($totalOmzet), 'Non-tunai: ' . rp($totalNonCash), 'cash') ?>
    <?= statcard('Potongan diskon member', rp($totalDiskon), 'Hemat oleh pelanggan', 'tag') ?>
    <?= statcard('Rata-rata basket size', rp($avgBasket), 'Per struk penjualan', 'list') ?>
</div>

<div class="card tbl">
    <div class="cap">
        <b><?= ic('list', 18) ?> Riwayat transaksi penjualan</b>
        <span class="mut">Menampilkan <?= $n ?> transaksi</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>No. transaksi</th>
                <th>Tanggal & jam</th>
                <th>Kasir ID</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th>Subtotal</th>
                <th>Diskon</th>
                <th>Total akhir</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filteredTransactions as $t): 
                $trxId = $t['transactionId'] ?? ($t['id'] ?? '-');
                $trxDate = date('Y-m-d H:i:s', strtotime((string)($t['transactionDate'] ?? ($t['ts'] ?? 'now'))));
                $cashierId = $t['cashierId'] ?? ($t['kasir'] ?? '-');
                $cust = !empty($t['customerName']) ? $t['customerName'] : (!empty($t['customerId']) ? $t['customerId'] : 'Pelanggan Umum');
                $methodRaw = strtoupper((string)($t['paymentMethod'] ?? 'CASH'));
                $methodLabel = ['CASH' => 'Tunai', 'QRIS' => 'QRIS', 'DEBIT' => 'Debit', 'TRANSFER' => 'Transfer'][$methodRaw] ?? $methodRaw;
                $subtotal = (int)($t['subtotal'] ?? 0);
                $discount = (int)($t['discountAmount'] ?? 0);
                $total = (int)($t['total'] ?? 0);
                $status = ($t['paymentStatus'] ?? '') === 'SUCCESS' ? 'Lunas' : ($t['paymentStatus'] ?? 'Lunas');
            ?>
                <tr>
                    <td data-l="No"><?= e($trxId) ?></td>
                    <td data-l="Waktu"><?= e($trxDate) ?></td>
                    <td data-l="Kasir"><?= e($cashierId) ?></td>
                    <td data-l="Pelanggan"><?= e($cust) ?></td>
                    <td data-l="Metode"><?= e($methodLabel) ?></td>
                    <td data-l="Subtotal"><?= rp($subtotal) ?></td>
                    <td data-l="Diskon"><?= rp($discount) ?></td>
                    <td data-l="Total"><b><?= rp($total) ?></b></td>
                    <td data-l="Status"><span class="pill Aman"><?= e($status) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$n): ?>
                <tr>
                    <td colspan="9" class="empty">Belum ada riwayat transaksi yang cocok dengan filter yang dipilih.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout/foot.php'; ?>
