<?php
declare(strict_types=1);

/**
 * Isi halaman Katalog Barang (dipakai halaman manager dan panel di halaman kasir).
 * Variabel: $products, $categories, $totalPhysicalStock, $totalAssetValue, $lowStockCount, $oosCount
 * Opsional : $panel (bool, true = tampil sebagai panel di halaman POS)
 * Dialog #mk dan #dlgCat harus disertakan oleh halaman pemanggil (partials/catalog_dialogs.php).
 */
$panel = $panel ?? false;
$pq = $panel ? 'kq' : 'q';
$ps = $panel ? 'ks' : 's';
$back = $panel ? '?page=pos#katalog' : '?page=inventory';
$mustKet = \PosClient\Http::isCashier();

$q = strtolower(trim((string)($_GET[$pq] ?? '')));
$s = trim((string)($_GET[$ps] ?? ''));

$rows = array_filter($products, function (\PosClient\Models\Product $p) use ($q, $s) {
    $matchesQ = ($q === '' || str_contains(strtolower($p->getName() . $p->getBarcode()), $q));
    $matchesS = ($s === '' || pstat($p->getStock(), $p->getMinStock()) === $s);
    return $matchesQ && $matchesS;
});
?>
<div class="bar">
    <p class="mut">Kelola harga beli/jual, audit stok fisik, dan peringatan restok.</p>
    <div class="acts">
        <button type="button" class="btn ghost" onclick="document.getElementById('dlgCat').showModal()">
            <?= ic('tag', 16) ?> Tambah kategori baru
        </button>
        <button type="button" class="btn" onclick="document.getElementById('mk').showModal()">
            <?= ic('plus', 16) ?> Tambah barang baru
        </button>
    </div>
</div>

<div class="stats">
    <?= statcard('Total SKU', count($products) . ' SKU', 'Aktif dalam katalog', 'layers') ?>
    <?= statcard('Total fisik gudang', $totalPhysicalStock . ' pcs', 'Nilai ' . rp($totalAssetValue), 'box') ?>
    <?= statcard('Stok menipis', $lowStockCount . ' produk', 'Stok di bawah batas minimum', 'alert') ?>
    <?= statcard('Barang habis', $oosCount . ' SKU', 'Tidak bisa dijual di kasir', 'bag') ?>
</div>

<form class="card filt" method="get" action="<?= $panel ? '?#katalog' : '' ?>">
    <input type="hidden" name="page" value="<?= $panel ? 'pos' : 'inventory' ?>">
    <label class="f grow">
        <span class="lb"><?= ic('search', 15) ?> Cari produk</span>
        <input name="<?= $pq ?>" value="<?= e($_GET[$pq] ?? '') ?>" placeholder="Nama produk atau barcode/SKU">
    </label>
    <label class="f">
        <span class="lb"><?= ic('filter', 15) ?> Status stok</span>
        <select name="<?= $ps ?>">
            <option value="">Semua status stok</option>
            <?php foreach (['Aman', 'Menipis', 'Habis'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= $s === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn" type="submit"><?= ic('search', 16) ?> Cari</button>
</form>

<div class="card tbl">
    <table>
        <thead>
            <tr>
                <th><?= ic('barcode', 15) ?> Barcode/SKU</th>
                <th>Nama produk</th>
                <th>Harga beli</th>
                <th>Harga jual</th>
                <th>Stok fisik</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Aksi gudang</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $p):
                $st = pstat($p->getStock(), $p->getMinStock());
                $fid = 'sf' . $p->getId();
            ?>
                <tr>
                    <td data-l="SKU"><?= e($p->getBarcode()) ?></td>
                    <td data-l="Produk"><b><?= e($p->getName()) ?></b></td>
                    <td data-l="Beli"><?= rp($p->getBuyPrice()) ?></td>
                    <td data-l="Jual"><?= rp($p->getSellPrice()) ?></td>
                    <td data-l="Stok"><?= $p->getStock() ?></td>
                    <td data-l="Status"><span class="pill <?= e($st) ?>"><?= e($st) ?></span></td>
                    <td data-l="Keterangan">
                        <input form="<?= e($fid) ?>" name="ket" class="ket" maxlength="150"
                               placeholder="<?= $mustKet ? 'Wajib: alasan ubah stok' : 'Alasan (opsional)' ?>"
                               <?= $mustKet ? 'required' : '' ?>>
                    </td>
                    <td data-l="Aksi">
                        <form method="post" action="?page=do-update-stock" class="inl" id="<?= e($fid) ?>">
                            <input type="hidden" name="id" value="<?= e($p->getId()) ?>">
                            <input type="hidden" name="back" value="<?= e($back) ?>">
                            <input type="number" name="qty" value="10" class="sm" title="Jumlah (boleh negatif)">
                            <button class="btn ghost" type="submit" title="Koreksi stok barang">
                                <?= ic('layers', 16) ?> Ubah stok
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="8" class="empty">Belum ada barang yang cocok. Tambahkan barang baru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
