<?php
declare(strict_types=1);

/**
 * Alat Diagnostik & Pengujian Koneksi REST API Ktor
 * Buka di browser: http://localhost:8000/cek-api.php
 */

require_once __DIR__ . '/src/Config.php';

$apiBase = \PosClient\Config::getApiBaseUrl();
$cli = PHP_SAPI === 'cli';
$user = $cli ? ($argv[1] ?? 'admin') : ($_POST['u'] ?? 'admin');
$pass = $cli ? ($argv[2] ?? 'admin123') : ($_POST['p'] ?? 'admin123');
$run = $cli || $_SERVER['REQUEST_METHOD'] === 'POST';
$out = [];

function callApi(string $method, string $path, ?array $body = null, ?string $token = null): array {
    global $apiBase;
    $url = rtrim($apiBase, '/') . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $t = microtime(true);
    $raw = curl_exec($ch);
    $ms = round((microtime(true) - $t) * 1000);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    $decoded = json_decode((string)$raw, true);
    $ok = ($status >= 200 && $status < 300);
    return [
        'ok' => $ok,
        'status' => $status,
        'ms' => $ms,
        'data' => $decoded,
        'msg' => $raw === false ? "Koneksi gagal: {$err}" : ($decoded['error']['message'] ?? "HTTP {$status}"),
        'req' => "{$method} {$url}"
    ];
}

if ($run) {
    $l = callApi('POST', '/auth/login', ['username' => $user, 'password' => $pass]);
    $out[] = [
        'label' => 'Login Autentikasi',
        'req' => $l['req'],
        'status' => $l['status'],
        'ok' => $l['ok'],
        'ms' => $l['ms'],
        'note' => $l['ok'] ? mb_substr(json_encode($l['data'], JSON_UNESCAPED_UNICODE), 0, 180) : $l['msg']
    ];

    $token = $l['data']['data']['accessToken'] ?? ($l['data']['accessToken'] ?? null);

    if ($l['ok']) {
        foreach ([
            ['Kategori Barang', 'GET', '/categories', null],
            ['Katalog Produk', 'GET', '/products', null],
            ['Stok Menipis', 'GET', '/inventory/low-stock', null],
            ['Data Pelanggan', 'GET', '/customers', null],
            ['Riwayat Transaksi', 'GET', '/transactions?limit=5', null],
        ] as $item) {
            $res = callApi($item[1], $item[2], $item[3], $token);
            $out[] = [
                'label' => $item[0],
                'req' => $res['req'],
                'status' => $res['status'],
                'ok' => $res['ok'],
                'ms' => $res['ms'],
                'note' => $res['ok'] ? mb_substr(json_encode($res['data'], JSON_UNESCAPED_UNICODE), 0, 180) : $res['msg']
            ];
        }
    }
}

if ($cli) {
    foreach ($out as $o) {
        printf("[%s] %-20s HTTP %-3s %4dms  %s\n      %s\n", $o['ok'] ? 'OK ' : 'ERR', $o['label'], $o['status'], $o['ms'], $o['req'], $o['note']);
    }
    exit;
}

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek Koneksi API Ktor</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .test-box { max-width: 900px; margin: 3rem auto; padding: 2rem; background: #fff; border-radius: 14px; border: 1px solid var(--line); }
        .ok { color: var(--p); font-weight: bold; }
        .err { color: var(--bad); font-weight: bold; }
        code { background: #edf4f1; padding: .2rem .4rem; border-radius: 6px; font-size: .85rem; }
    </style>
</head>
<body style="background: var(--bg); padding: 1rem;">
<div class="test-box">
    <h1>Cek Koneksi API Ktor</h1>
    <p class="mut">Backend Endpoint Target: <code><?= $h($apiBase) ?></code></p>
    <form method="post" class="card filt" style="margin: 1.5rem 0;">
        <label class="f">
            <span class="lb">Username</span>
            <input name="u" value="<?= $h($user) ?>">
        </label>
        <label class="f">
            <span class="lb">Password</span>
            <input name="p" type="password" value="<?= $h($pass) ?>">
        </label>
        <button class="btn" type="submit">Jalankan Tes API</button>
    </form>

    <?php if ($out): ?>
    <div class="card tbl">
        <table>
            <thead>
                <tr>
                    <th>Tes Modul</th>
                    <th>Status</th>
                    <th>Latency</th>
                    <th>Request & Snapshot Hasil</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($out as $o): ?>
                <tr>
                    <td data-l="Tes"><b><?= $h($o['label']) ?></b></td>
                    <td data-l="Status"><span class="<?= $o['ok'] ? 'ok' : 'err' ?>"><?= $o['ok'] ? 'OK (HTTP ' . $h($o['status']) . ')' : 'GAGAL (HTTP ' . $h($o['status']) . ')' ?></span></td>
                    <td data-l="Waktu"><?= $o['ms'] ?> ms</td>
                    <td data-l="Detail">
                        <code><?= $h($o['req']) ?></code><br>
                        <small class="mut"><?= $h($o['note']) ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <div style="margin-top: 1.5rem;">
        <a class="btn ghost" href="?page=pos">Kembali ke Terminal POS</a>
    </div>
</div>
</body>
</html>
