<?php
declare(strict_types=1);
/** Dialog Tambah Barang (#mk) dan Tambah Kategori (#dlgCat). Variabel: $categories, opsional $back */
$back = $back ?? '?page=inventory';
?>
<dialog id="mk">
    <form method="post" action="?page=do-create-product">
        <input type="hidden" name="back" value="<?= e($back) ?>">
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
                <button type="button" onclick="document.getElementById('mk').close(); document.getElementById('dlgCat').showModal();" style="background:none; border:none; padding:0; font-size:12px; color:var(--p); text-decoration:underline; cursor:pointer;">
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
            <label class="f">Harga beli<input type="number" name="buy" min="0" required placeholder="0"></label>
            <label class="f">Harga jual<input type="number" name="sell" min="0" required placeholder="0"></label>
        </div>
        <div class="two">
            <label class="f">Stok awal<input type="number" name="stock" min="0" value="0"></label>
            <label class="f">Stok minimum<input type="number" name="min" min="0" value="5"></label>
        </div>
        <div class="acts">
            <button type="button" class="btn ghost" onclick="document.getElementById('mk').close()"><?= ic('x', 16) ?> Batal</button>
            <button class="btn" type="submit"><?= ic('save', 16) ?> Simpan barang</button>
        </div>
    </form>
</dialog>

<dialog id="dlgCat">
    <form method="post" action="?page=do-create-category">
        <input type="hidden" name="back" value="<?= e($back) ?>">
        <h3><?= ic('tag', 20) ?> Tambah kategori baru</h3>
        <p class="mut" style="font-size:12px; margin:4px 0 12px 0;">Kategori membantu mengelompokkan produk di katalog dan kasir POS.</p>
        <label class="f">
            Nama kategori
            <input name="name" required placeholder="Contoh: Susu & Olahan, Biskuit, Elektronik">
        </label>
        <div class="acts" style="margin-top:16px;">
            <button type="button" class="btn ghost" onclick="document.getElementById('dlgCat').close()"><?= ic('x', 16) ?> Batal</button>
            <button class="btn" type="submit"><?= ic('save', 16) ?> Simpan kategori</button>
        </div>
    </form>
</dialog>
