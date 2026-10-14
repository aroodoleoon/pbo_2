<?php
declare(strict_types=1);

namespace PosClient\Controllers;

use PosClient\ApiClient;
use PosClient\Session;

class ReportController
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

        $startDate = (string)($_GET['from'] ?? date('Y-m-01'));
        $endDate = (string)($_GET['to'] ?? date('Y-m-d'));
        $cashierFilter = (string)($_GET['cashier'] ?? 'all');
        $paymentFilter = (string)($_GET['method'] ?? 'all');

        // Ambil riwayat transaksi
        $trxResp = $this->api->get('/transactions', ['limit' => 200]);
        $transactions = $trxResp->isSuccess() ? ($trxResp->getData() ?? []) : [];

        // Filter transaksi berdasarkan kasir dan metode
        $filteredTransactions = array_filter($transactions, function($t) use ($cashierFilter, $paymentFilter) {
            if ($cashierFilter !== 'all' && ($t['cashierId'] ?? '') !== $cashierFilter) {
                return false;
            }
            if ($paymentFilter !== 'all' && strtoupper($t['paymentMethod'] ?? '') !== strtoupper($paymentFilter)) {
                return false;
            }
            return true;
        });

        // Hitung KPIs transaksi
        $totalTransactions = count($filteredTransactions);
        $totalOmzet = 0;
        $totalDiskon = 0;
        $totalCash = 0;
        $totalNonCash = 0;

        foreach ($filteredTransactions as $t) {
            $total = (int)($t['total'] ?? 0);
            $totalOmzet += $total;
            $totalDiskon += (int)($t['discountAmount'] ?? 0);
            if (strtoupper($t['paymentMethod'] ?? '') === 'CASH') {
                $totalCash += $total;
            } else {
                $totalNonCash += $total;
            }
        }

        $avgPerTransaction = $totalTransactions > 0 ? (int)round($totalOmzet / $totalTransactions) : 0;

        // Ambil rekap harian jika role ADMIN
        $salesSummary = [];
        $summaryResp = $this->api->get('/reports/sales', ['from' => $startDate, 'to' => $endDate]);
        if ($summaryResp->isSuccess() && is_array($summaryResp->getData())) {
            $salesSummary = $summaryResp->getData();
        }

        $user = Session::getAuthUser();

        require __DIR__ . '/../../views/reports.php';
    }

    public function exportCsv(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: ?page=login');
            exit;
        }

        $trxResp = $this->api->get('/transactions', ['limit' => 500]);
        $transactions = $trxResp->isSuccess() ? ($trxResp->getData() ?? []) : [];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan_penjualan_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID Transaksi', 'Tanggal Waktu', 'Kasir ID', 'Pelanggan ID', 'Subtotal', 'Diskon', 'Total', 'Metode Bayar', 'Jumlah Dibayar', 'Kembalian', 'Status']);

        foreach ($transactions as $row) {
            fputcsv($output, [
                $row['transactionId'] ?? '',
                $row['transactionDate'] ?? '',
                $row['cashierId'] ?? '',
                $row['customerId'] ?? '-',
                $row['subtotal'] ?? 0,
                $row['discountAmount'] ?? 0,
                $row['total'] ?? 0,
                $row['paymentMethod'] ?? '',
                $row['amountPaid'] ?? 0,
                $row['changeAmount'] ?? 0,
                $row['paymentStatus'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }
}
