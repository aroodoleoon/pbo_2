<?php
declare(strict_types=1);

$pageTitle = 'Kelola Kasir';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

// Data kasir lokal / demo jika endpoint backend belum tersedia
$cashiersFile = __DIR__ . '/../data/cashiers.json';
if (!file_exists(dirname($cashiersFile))) {
    @mkdir(dirname($cashiersFile), 0777, true);
}
if (!file_exists($cashiersFile)) {
    $defaultCashiers = [
        ['id' => 'kasir1', 'name' => 'Kasir Shift 1', 'role' => 'kasir', 'shift' => 'Pagi', 'active' => true, 'created' => date('Y-m-d')],
        ['id' => 'kasir2', 'name' => 'Kasir Shift 2', 'role' => 'kasir', 'shift' => 'Siang', 'active' => true, 'created' => date('Y-m-d')],
        ['id' => 'kasir3', 'name' => 'Kasir Shift 3', 'role' => 'kasir', 'shift' => 'Malam', 'active' => false, 'created' => date('Y-m-d')],
    ];
    @file_put_contents($cashiersFile, json_encode($defaultCashiers, JSON_PRETTY_PRINT));
}

$cashiers = json_decode(@file_get_contents($cashiersFile) ?: '[]', true) ?: [];

// Handling post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $id = strtolower(trim((string)($_POST['id'] ?? '')));

    if ($act === 'add') {
        $nm = trim((string)($_POST['name'] ?? ''));
        $un = strtolower(trim((string)($_POST['username'] ?? '')));
        $shift = in_array($_POST['shift'] ?? '', ['Pagi', 'Siang', 'Malam']) ? $_POST['shift'] : 'Pagi';

        if ($nm && preg_match('/^[a-z0-9_]{3,20}$/', $un)) {
            $exists = false;
            foreach ($cashiers as $c) {
                if ($c['id'] === $un) { $exists = true; break; }
            }
            if (!$exists) {
                $cashiers[] = [
                    'id' => $un,
                    'name' => $nm,
                    'role' => 'kasir',
                    'shift' => $shift,
                    'active' => true,
                    'created' => date('Y-m-d')
                ];
                file_put_contents($cashiersFile, json_encode($cashiers, JSON_PRETTY_PRINT));
                \PosClient\Session::setFlash('success', "Kasir {$nm} berhasil ditambahkan.");
            } else {
                \PosClient\Session::setFlash('error', "Username '{$un}' sudah terdaftar.");
            }
        }
    } elseif ($act === 'toggle') {
        foreach ($cashiers as $i => $c) {
            if ($c['id'] === $id) {
                $cashiers[$i]['active'] = !$c['active'];
                file_put_contents($cashiersFile, json_encode($cashiers, JSON_PRETTY_PRINT));
                \PosClient\Session::setFlash('success', $c['active'] ? 'Kasir dinonaktifkan.' : 'Kasir diaktifkan kembali.');
                break;
            }
        }
    } elseif ($act === 'del') {
        foreach ($cashiers as $i => $c) {
            if ($c['id'] === $id) {
                array_splice($cashiers, $i, 1);
                file_put_contents($cashiersFile, json_encode($cashiers, JSON_PRETTY_PRINT));
                \PosClient\Session::setFlash('success', 'Akun kasir berhasil dihapus.');
                break;
            }
        }
    }
    header('Location: ?page=cashiers');
    exit;
}

$q = strtolower(trim((string)($_GET['q'] ?? '')));
$s = trim((string)($_GET['s'] ?? ''));

$rows = array_filter($cashiers, function ($c) use ($q, $s) {
    $matchQ = ($q === '' || str_contains(strtolower($c['name'] . $c['id']), $q));
    $matchS = ($s === '' || ($s === 'Aktif' ? $c['active'] : !$c['active']));
    return $matchQ && $matchS;
});

$totalKasir = count($cashiers);
$activeKasir = count(array_filter($cashiers, fn($c) => $c['active']));
?>
<div class="bar">
    <p class="mut">Tambah akun kasir, atur shift, dan pantau status akun kasir terminal.</p>
    <div class="acts">
        <button type="button" class="btn" onclick="document.getElementById('ma').showModal()">
            <?= ic('userplus', 16) ?> Tambah kasir
        </button>
    </div>
</div>

<div class="stats">
    <?= statcard('Total kasir', $totalKasir . ' akun', 'Terdaftar di sistem', 'users') ?>
    <?= statcard('Kasir aktif', $activeKasir . ' akun', 'Bisa login ke terminal', 'shield') ?>
    <?= statcard('Shift operasional', '3 Shift', 'Pagi, Siang, Malam', 'clock') ?>
    <?= statcard('Keamanan terminal', 'Terisolasi', 'Akses dibatasi per role', 'lock') ?>
</div>

<form class="card filt" method="get" action="">
    <input type="hidden" name="page" value="cashiers">
    <label class="f grow">
        <span class="lb"><?= ic('search', 15) ?> Cari kasir</span>
        <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Nama atau username">
    </label>
    <label class="f">
        <span class="lb"><?= ic('filter', 15) ?> Status akun</span>
        <select name="s">
            <option value="">Semua status</option>
            <option value="Aktif" <?= $s === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $s === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </label>
    <button class="btn" type="submit"><?= ic('search', 16) ?> Cari</button>
</form>

<div class="card tbl">
    <table>
        <thead>
            <tr>
                <th>Kasir</th>
                <th><?= ic('clock', 15) ?> Shift</th>
                <th>Status</th>
                <th>Tanggal daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $c): ?>
                <tr>
                    <td data-l="Kasir">
                        <b><?= e($c['name']) ?></b>
                        <small>@<?= e($c['id']) ?></small>
                    </td>
                    <td data-l="Shift"><?= e($c['shift']) ?></td>
                    <td data-l="Status">
                        <span class="pill <?= $c['active'] ? 'Aman' : 'Habis' ?>">
                            <?= $c['active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td data-l="Daftar"><?= e($c['created'] ?? '-') ?></td>
                    <td data-l="Aksi">
                        <div class="inl">
                            <form method="post" action="?page=cashiers">
                                <input type="hidden" name="act" value="toggle">
                                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                                <button class="iconbtn" type="submit" title="<?= $c['active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                    <?= ic('power', 17) ?>
                                </button>
                            </form>
                            <form method="post" action="?page=cashiers" onsubmit="return confirm('Hapus akun kasir ini?')">
                                <input type="hidden" name="act" value="del">
                                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                                <button class="iconbtn dng" type="submit" title="Hapus">
                                    <?= ic('trash', 17) ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="5" class="empty">Belum ada kasir yang cocok. Tambahkan akun kasir baru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah Kasir -->
<dialog id="ma">
    <form method="post" action="?page=cashiers">
        <h3><?= ic('userplus', 20) ?> Tambah kasir baru</h3>
        <input type="hidden" name="act" value="add">
        <label class="f">
            Nama lengkap
            <input name="name" required placeholder="Nama lengkap petugas kasir">
        </label>
        <label class="f">
            Username
            <input name="username" required pattern="[a-z0-9_]{3,20}" placeholder="huruf kecil, angka, _">
        </label>
        <label class="f">
            Password
            <input name="password" type="password" minlength="6" required placeholder="Minimal 6 karakter">
        </label>
        <label class="f">
            Shift kerja
            <select name="shift">
                <option value="Pagi">Pagi (07:00 - 15:00)</option>
                <option value="Siang">Siang (15:00 - 22:00)</option>
                <option value="Malam">Malam (22:00 - 07:00)</option>
            </select>
        </label>
        <div class="acts">
            <button type="button" class="btn ghost" onclick="document.getElementById('ma').close()">
                <?= ic('x', 16) ?> Batal
            </button>
            <button class="btn" type="submit">
                <?= ic('save', 16) ?> Simpan kasir
            </button>
        </div>
    </form>
</dialog>

<?php require_once __DIR__ . '/layout/foot.php'; ?>
