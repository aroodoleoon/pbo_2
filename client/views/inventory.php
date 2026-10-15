<?php
declare(strict_types=1);

$pageTitle = 'Katalog Barang';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Product[] $products */
/** @var array $categories */
/** @var array $lowStockList */
/** @var array $movements */
/** @var int $totalSku */
/** @var int $totalPhysicalStock */
/** @var int $totalAssetValue */
/** @var int $lowStockCount */
/** @var int $oosCount */
/** @var array $user */

$q = strtolower(trim((string)($_GET['q'] ?? '')));
$s = trim((string)($_GET['s'] ?? ''));

$rows = array_filter($products, function (\PosClient\Models\Product $p) use ($q, $s) {
    $matchesQ = ($q === '' || str_contains(strtolower($p->getName() . $p->getBarcode()), $q));
    $status = pstat($p->getStock(), $p->getMinStock());
    $matchesS = ($s === '' || $status === $s);
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

<form class="card filt" method="get" action="">
    <input type="hidden" name="page" value="inventory">
    <label class="f grow">
        <span class="lb"><?= ic('search', 15) ?> Cari produk</span>
        <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Nama produk atau barcode/SKU">
    </label>
    <label class="f">
        <span class="lb"><?= ic('filter', 15) ?> Status stok</span>
        <select name="s">
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
                <th>Aksi gudang</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $p): 
                $st = pstat($p->getStock(), $p->getMinStock());
            ?>
                <tr>
                    <td data-l="SKU"><?= e($p->getBarcode()) ?></td>
                    <td data-l="Produk"><b><?= e($p->getName()) ?></b></td>
                    <td data-l="Beli"><?= rp($p->getBuyPrice()) ?></td>
                    <td data-l="Jual"><?= rp($p->getSellPrice()) ?></td>
                    <td data-l="Stok"><?= $p->getStock() ?></td>
                    <td data-l="Status"><span class="pill <?= e($st) ?>"><?= e($st) ?></span></td>
                    <td data-l="Aksi">
                        <form method="post" action="?page=do-update-stock" class="inl">
                            <input type="hidden" name="id" value="<?= e($p->getId()) ?>">
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
                    <td colspan="7" class="empty">Belum ada barang yang cocok. Tambahkan barang baru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Dialog Tambah Barang Baru -->
<dialog id="mk">
    <form method="post" action="?page=do-create-product">
        <h3><?= ic('box', 20) ?> Tambah barang baru</h3>
        <label class="f">
            Barcode/SKU
            <input name="sku" required placeholder="Contoh: 89999991001">
        </label>
        <label class="f">
            Nama produk
            <input name="name" required placeholder="Nama lengkap produk">
        </label>
        <label class="f">
            <span style="display:flex; justify-content:space-between; align-items:center;">
                <span>Kategori</span>
                <button type="button" onclick="document.getElementById('mk').close(); document.getElementById('dlgCat').showModal();" style="background:none; border:none; padding:0; font-size:12px; color:var(--pri, #006a60); text-decoration:underline; cursor:pointer;">
                    + Kategori baru
                </button>
            </span>
            <select name="cat">
                <option value="">(Tanpa Kategori / Default)</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="two">
            <label class="f">
                Harga beli
                <input type="number" name="buy" min="0" required placeholder="0">
            </label>
            <label class="f">
                Harga jual
                <input type="number" name="sell" min="0" required placeholder="0">
            </label>
        </div>
        <div class="two">
            <label class="f">
                Stok awal
                <input type="number" name="stock" min="0" value="0">
            </label>
            <label class="f">
                Stok minimum
                <input type="number" name="min" min="0" value="5">
            </label>
        </div>
        <div class="acts">
            <button type="button" class="btn ghost" onclick="document.getElementById('mk').close()">
                <?= ic('x', 16) ?> Batal
            </button>
            <button class="btn" type="submit">
                <?= ic('save', 16) ?> Simpan barang
            </button>
        </div>
    </form>
</dialog>

<!-- Modal Dialog Tambah Kategori Baru -->
<dialog id="dlgCat">
    <form method="post" action="?page=do-create-category">
        <h3><?= ic('tag', 20) ?> Tambah kategori baru</h3>
        <p class="mut" style="font-size:12px; margin:4px 0 12px 0;">Kategori membantu mengelompokkan produk di katalog dan kasir POS.</p>
        <label class="f">
            Nama kategori
            <input name="name" required placeholder="Contoh: Susu & Olahan, Biskuit, Elektronik" autofocus>
        </label>
        <div class="acts" style="margin-top:16px;">
            <button type="button" class="btn ghost" onclick="document.getElementById('dlgCat').close()">
                <?= ic('x', 16) ?> Batal
            </button>
            <button class="btn" type="submit">
                <?= ic('save', 16) ?> Simpan kategori
            </button>
        </div>
    </form>
</dialog>

<?php require_once __DIR__ . '/layout/foot.php'; ?>
