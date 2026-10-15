<?php
declare(strict_types=1);

$pageTitle = 'Kelola Kasir';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var array $cashiers */
/** @var int $totalKasir */
/** @var int $activeKasir */

$q = strtolower(trim((string)($_GET['q'] ?? '')));
$s = trim((string)($_GET['s'] ?? ''));

$rows = array_filter($cashiers, function ($c) use ($q, $s) {
    $matchQ = ($q === '' || str_contains(strtolower($c['name'] . ($c['username'] ?? '')), $q));
    $matchS = ($s === '' || ($s === 'Aktif' ? $c['active'] : !$c['active']));
    return $matchQ && $matchS;
});
?>
<div class="bar">
    <p class="mut">Tambah akun kasir, atur akses, dan pantau status akun kasir terminal.</p>
    <div class="acts">
        <button type="button" class="btn" onclick="document.getElementById('ma').showModal()">
            <?= ic('userplus', 16) ?> Tambah kasir
        </button>
    </div>
</div>

<div class="stats">
    <?= statcard('Total kasir', $totalKasir . ' akun', 'Terdaftar di database', 'users') ?>
    <?= statcard('Kasir aktif', $activeKasir . ' akun', 'Dapat login ke kasir POS', 'check') ?>
    <?= statcard('Nonaktif', ($totalKasir - $activeKasir) . ' akun', 'Akses login diblokir', 'power') ?>
    <?= statcard('Peran akun', 'Admin & Kasir', 'Hak akses bertingkat', 'shield') ?>
</div>

<form class="card filt" method="get" action="">
    <input type="hidden" name="page" value="cashiers">
    <label class="f grow">
        <span class="lb"><?= ic('search', 15) ?> Cari kasir</span>
        <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Nama lengkap atau username">
    </label>
    <label class="f">
        <span class="lb"><?= ic('filter', 15) ?> Status</span>
        <select name="s">
            <option value="">Semua status</option>
            <option value="Aktif" <?= $s === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $s === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </label>
    <button class="btn" type="submit"><?= ic('search', 16) ?> Saring</button>
</form>

<div class="card tbl">
    <table>
        <thead>
            <tr>
                <th>Petugas Kasir</th>
                <th>Peran</th>
                <th>Status</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $c): ?>
                <tr>
                    <td data-l="Kasir">
                        <b><?= e($c['name']) ?></b>
                        <small>@<?= e($c['username']) ?></small>
                    </td>
                    <td data-l="Peran">
                        <span class="pill <?= ($c['role'] ?? '') === 'ADMIN' ? 'Aman' : 'Menipis' ?>">
                            <?= e($c['role'] ?? 'CASHIER') ?>
                        </span>
                    </td>
                    <td data-l="Status">
                        <span class="pill <?= $c['active'] ? 'Aman' : 'Habis' ?>">
                            <?= $c['active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td data-l="Daftar"><?= e($c['created'] ?? '-') ?></td>
                    <td data-l="Aksi">
                        <div class="inl">
                            <form method="post" action="?page=do-toggle-cashier">
                                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                                <button class="iconbtn" type="submit" title="<?= $c['active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                    <?= ic('power', 17) ?>
                                </button>
                            </form>
                            <form method="post" action="?page=do-delete-cashier" onsubmit="return confirm('Hapus / Nonaktifkan akun kasir ini?')">
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
    <form method="post" action="?page=do-create-cashier">
        <h3><?= ic('userplus', 20) ?> Tambah kasir baru</h3>
        <label class="f">
            Nama lengkap
            <input name="name" required placeholder="Contoh: Budi Santoso">
        </label>
        <label class="f">
            Username
            <input name="username" required pattern="[a-z0-9_]{3,20}" placeholder="huruf kecil, angka, _ (contoh: budi_kasir)">
        </label>
        <label class="f">
            Password Login
            <input name="password" type="password" minlength="6" required placeholder="Minimal 6 karakter">
        </label>
        <div class="acts" style="margin-top: 16px;">
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
