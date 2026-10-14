<?php
declare(strict_types=1);

$pageTitle = 'Katalog & Manajemen Stok';
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/sidebar.php';
require __DIR__ . '/layout/header.php';

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
?>
<div class="px-gutter-lg py-space-xl flex flex-col gap-space-xl">
    <!-- Top Header & Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg">
        <div class="flex flex-col">
            <div class="flex items-center gap-space-sm mb-space-xs">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-container text-on-primary text-body-sm font-bold">
                    <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                </span>
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold">Modul Inventaris & Gudang (Alex)</span>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface font-bold tracking-tight">Katalog & Manajemen Stok Barang</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Kelola harga beli/jual, audit stok fisik, dan peringatan restok otomatis terintegrasi.</p>
        </div>
        <div class="flex items-center gap-space-md flex-wrap">
            <button type="button" onclick="openAddProductModal()"
                    class="inline-flex items-center gap-space-sm px-space-lg py-space-sm rounded-lg bg-primary-container text-on-primary font-body-md text-body-md font-semibold hover:bg-primary transition-all shadow-md hover:shadow-lg">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span>Tambah Barang Baru</span>
            </button>
        </div>
    </div>

    <!-- 4 KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-lg">
        <!-- Metric 1: Total SKU -->
        <div class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-space-lg shadow-sm flex flex-col justify-between border border-outline-variant/10">
            <div class="flex items-center justify-between mb-space-md">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">Total SKU Terdaftar</span>
                <span class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">category</span>
                </span>
            </div>
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-baseline gap-space-xs">
                    <span class="font-numeric-total text-numeric-total text-on-surface font-extrabold"><?= $totalSku ?></span>
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium">SKU</span>
                </div>
                <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md">
                    <span class="material-symbols-outlined text-[16px]">check</span>
                    <span>Aktif dalam katalog</span>
                </div>
            </div>
        </div>

        <!-- Metric 2: Total Fisik Gudang -->
        <div class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-space-lg shadow-sm flex flex-col justify-between border border-outline-variant/10">
            <div class="flex items-center justify-between mb-space-md">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">Total Fisik Gudang</span>
                <span class="w-10 h-10 rounded-xl bg-secondary-container/40 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[22px]">warehouse</span>
                </span>
            </div>
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-baseline gap-space-xs">
                    <span class="font-numeric-total text-numeric-total text-on-surface font-extrabold"><?= number_format($totalPhysicalStock, 0, ',', '.') ?></span>
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium">Pcs</span>
                </div>
                <div class="flex items-center gap-space-xs text-secondary font-label-md text-label-md font-semibold">
                    <span class="material-symbols-outlined text-[16px]">monetization_on</span>
                    <span>Nilai: Rp <?= number_format($totalAssetValue, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- Metric 3: Stok Menipis (Alert) -->
        <div class="relative overflow-hidden rounded-xl <?= $lowStockCount > 0 ? 'bg-error-container' : 'bg-surface-container-lowest' ?> p-space-lg shadow-sm flex flex-col justify-between border border-outline-variant/10">
            <div class="flex items-center justify-between mb-space-md">
                <span class="font-label-md text-label-md <?= $lowStockCount > 0 ? 'text-on-error-container' : 'text-on-surface-variant' ?> uppercase tracking-wider font-bold">Stok Menipis</span>
                <span class="w-10 h-10 rounded-xl <?= $lowStockCount > 0 ? 'bg-error text-on-error' : 'bg-tertiary-fixed/30 text-tertiary' ?> flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[22px]">warning</span>
                </span>
            </div>
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-baseline gap-space-xs">
                    <span class="font-numeric-total text-numeric-total <?= $lowStockCount > 0 ? 'text-on-error-container' : 'text-on-surface' ?> font-extrabold"><?= $lowStockCount ?></span>
                    <span class="font-body-md text-body-md <?= $lowStockCount > 0 ? 'text-on-error-container' : 'text-on-surface-variant' ?> font-semibold">Produk</span>
                </div>
                <?php if ($lowStockCount > 0): ?>
                <div class="inline-flex items-center gap-space-xs px-space-sm py-0.5 rounded-full bg-error text-on-error w-fit font-label-sm text-label-sm uppercase tracking-wide font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-surface-container-lowest animate-pulse"></span>
                    <span>Segera Restok!</span>
                </div>
                <?php else: ?>
                <div class="text-secondary font-label-md text-label-md font-semibold">Semua stok di atas ambang batas</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Metric 4: Out of Stock (OOS) -->
        <div class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-space-lg shadow-sm flex flex-col justify-between border border-outline-variant/10">
            <div class="flex items-center justify-between mb-space-md">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">Barang Habis (OOS)</span>
                <span class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-outline">
                    <span class="material-symbols-outlined text-[22px]">production_quantity_limits</span>
                </span>
            </div>
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-baseline gap-space-xs">
                    <span class="font-numeric-total text-numeric-total text-on-surface font-extrabold"><?= $oosCount ?></span>
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium">SKU</span>
                </div>
                <div class="flex items-center gap-space-xs text-outline font-label-md text-label-md">
                    <span>Non-aktif di kasir jika 0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Filter & Search Bar -->
    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm border border-outline-variant/10 flex flex-col md:flex-row items-center justify-between gap-space-md">
        <div class="relative flex-1 w-full">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
            <input type="text" id="inventorySearchInput" onkeyup="filterInventoryTable()"
                   class="w-full pl-10 pr-4 py-2 bg-surface-container-low rounded-lg text-sm text-on-surface outline-none focus:ring-2 focus:ring-primary"
                   placeholder="Cari berdasarkan nama produk atau barcode..."/>
        </div>
        <div class="flex items-center gap-space-sm w-full md:w-auto">
            <select id="stockStatusFilter" onchange="filterInventoryTable()"
                    class="bg-surface-container-low text-on-surface text-xs font-semibold px-space-md py-2.5 rounded-lg outline-none cursor-pointer">
                <option value="all">Semua Status Stok</option>
                <option value="low">Hanya Stok Menipis</option>
                <option value="oos">Hanya Barang Habis</option>
                <option value="safe">Stok Aman</option>
            </select>
        </div>
    </div>

    <!-- Inventory Products Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="inventoryTable">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/20 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                        <th class="py-3.5 px-4">Barcode / SKU</th>
                        <th class="py-3.5 px-4">Nama Produk</th>
                        <th class="py-3.5 px-4">Harga Beli</th>
                        <th class="py-3.5 px-4">Harga Jual</th>
                        <th class="py-3.5 px-4 text-center">Stok Fisik</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi Gudang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-sm">
                    <?php foreach ($products as $prod): ?>
                    <tr class="inv-row hover:bg-surface-container-low/50 transition-colors"
                        data-name="<?= htmlspecialchars(strtolower($prod->getName())) ?>"
                        data-barcode="<?= htmlspecialchars($prod->getBarcode()) ?>"
                        data-status="<?= $prod->isOutOfStock() ? 'oos' : ($prod->isLowStock() ? 'low' : 'safe') ?>">
                        <td class="py-3.5 px-4 font-mono font-semibold text-primary">
                            <?= htmlspecialchars($prod->getBarcode()) ?>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-on-surface">
                            <?= htmlspecialchars($prod->getName()) ?>
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant font-mono">
                            <?= $prod->getFormattedBuyPrice() ?>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-on-surface font-mono">
                            <?= $prod->getFormattedSellPrice() ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-numeric-md font-bold text-on-surface"><?= $prod->getStock() ?></span>
                            <span class="text-xs text-outline">/ min <?= $prod->getMinStock() ?></span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($prod->isOutOfStock()): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-error-container text-on-error-container text-xs font-bold">
                                <span class="material-symbols-outlined text-[14px]">block</span>
                                <span>Habis (0)</span>
                            </span>
                            <?php elseif ($prod->isLowStock()): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary-fixed text-on-tertiary-fixed text-xs font-bold">
                                <span class="material-symbols-outlined text-[14px]">warning</span>
                                <span>Menipis</span>
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-semibold">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                <span>Aman</span>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" onclick="openRestockModal('<?= htmlspecialchars($prod->getId()) ?>', '<?= htmlspecialchars(addslashes($prod->getName())) ?>')"
                                        class="px-2.5 py-1.5 rounded-lg bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant text-xs font-bold transition-all shadow-sm flex items-center gap-1"
                                        title="Penerimaan Barang Masuk">
                                    <span class="material-symbols-outlined text-[16px]">add_box</span>
                                    <span>Restok</span>
                                </button>
                                <button type="button" onclick="openAdjustModal('<?= htmlspecialchars($prod->getId()) ?>', '<?= htmlspecialchars(addslashes($prod->getName())) ?>')"
                                        class="px-2.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold transition-all flex items-center gap-1"
                                        title="Penyesuaian Rusak / Hilang">
                                    <span class="material-symbols-outlined text-[16px]">tune</span>
                                    <span>Koreksi</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL RESTOK MASUK ================= -->
<div class="fixed inset-0 bg-inverse-surface/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="restockModal">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-md w-full shadow-2xl border border-outline-variant/30 flex flex-col gap-4 animate-in zoom-in-95">
        <div class="flex items-center justify-between border-b border-outline-variant/20 pb-3">
            <div class="flex flex-col">
                <span class="font-headline-md font-bold text-on-surface">Penerimaan Stok Masuk</span>
                <span class="text-xs text-outline" id="restockProdName">-</span>
            </div>
            <button type="button" onclick="closeRestockModal()" class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="?page=do-inventory-receipt" method="POST" class="flex flex-col gap-3">
            <input type="hidden" name="productId" id="restockProdId"/>
            
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Jumlah Masuk (Pcs)</label>
                <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface font-bold text-base focus:ring-2 focus:ring-primary outline-none" placeholder="Contoh: 24"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Sumber Supplier</label>
                <input type="text" name="source" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none" value="PT Distributor Utama"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Catatan / No. Surat Jalan</label>
                <input type="text" name="notes" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none" placeholder="SJ-2026-xxx"/>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeRestockModal()" class="w-1/3 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-bold">Batal</button>
                <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-secondary text-on-secondary font-bold text-xs shadow-md">Simpan Stok Masuk</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL KOREKSI / ADJUST ================= -->
<div class="fixed inset-0 bg-inverse-surface/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="adjustModal">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-md w-full shadow-2xl border border-outline-variant/30 flex flex-col gap-4 animate-in zoom-in-95">
        <div class="flex items-center justify-between border-b border-outline-variant/20 pb-3">
            <div class="flex flex-col">
                <span class="font-headline-md font-bold text-on-surface">Penyesuaian Stok Keluar</span>
                <span class="text-xs text-outline" id="adjustProdName">-</span>
            </div>
            <button type="button" onclick="closeAdjustModal()" class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="?page=do-inventory-adjust" method="POST" class="flex flex-col gap-3">
            <input type="hidden" name="productId" id="adjustProdId"/>
            
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Jumlah Keluar (Pcs)</label>
                <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface font-bold text-base focus:ring-2 focus:ring-error outline-none" placeholder="Contoh: 2"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Alasan Penyesuaian</label>
                <select name="type" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none">
                    <option value="OUT_DAMAGED">Barang Rusak / Kadaluwarsa (OUT_DAMAGED)</option>
                    <option value="OUT_LOST">Barang Hilang / Selisih Fisik (OUT_LOST)</option>
                    <option value="OUT_OTHER">Keperluan Toko / Sampel (OUT_OTHER)</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Keterangan Tambahan</label>
                <input type="text" name="reason" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none" placeholder="Kemasan rusak tertimpa kardus"/>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeAdjustModal()" class="w-1/3 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-bold">Batal</button>
                <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-error text-on-error font-bold text-xs shadow-md">Catat Stok Keluar</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL TAMBAH BARANG BARU ================= -->
<div class="fixed inset-0 bg-inverse-surface/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="addProductModal">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-outline-variant/30 flex flex-col gap-4 animate-in zoom-in-95">
        <div class="flex items-center justify-between border-b border-outline-variant/20 pb-3">
            <span class="font-headline-md font-bold text-on-surface">Tambah Barang Baru ke Katalog</span>
            <button type="button" onclick="closeAddProductModal()" class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="?page=do-create-product" method="POST" class="flex flex-col gap-3">
            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold uppercase text-outline">Barcode (EAN-13)</label>
                    <input type="text" name="barcode" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface font-mono text-sm outline-none" placeholder="899886620025"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold uppercase text-outline">Kategori</label>
                    <select name="categoryId" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none">
                        <option value="">-- Tanpa Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Nama Lengkap Barang</label>
                <input type="text" name="name" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none" placeholder="Contoh: Teh Pucuk Harum 350ml"/>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold uppercase text-outline">Harga Beli (Modal)</label>
                    <input type="number" name="buyPrice" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none" placeholder="3000"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold uppercase text-outline">Harga Jual (Kasir)</label>
                    <input type="number" name="sellPrice" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none font-bold text-primary" placeholder="4500"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold uppercase text-outline">Stok Awal</label>
                    <input type="number" name="initialStock" value="20" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold uppercase text-outline">Ambang Stok Minimum</label>
                    <input type="number" name="minStock" value="5" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none"/>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeAddProductModal()" class="w-1/3 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-bold">Batal</button>
                <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-xs shadow-md">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterInventoryTable() {
    const q = document.getElementById('inventorySearchInput').value.toLowerCase().trim();
    const status = document.getElementById('stockStatusFilter').value;
    const rows = document.querySelectorAll('.inv-row');

    rows.forEach(row => {
        const name = row.dataset.name;
        const barcode = row.dataset.barcode;
        const rowStatus = row.dataset.status;

        const matchQuery = (name.includes(q) || barcode.includes(q));
        const matchStatus = (status === 'all' || rowStatus === status);

        if (matchQuery && matchStatus) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

function openRestockModal(id, name) {
    document.getElementById('restockProdId').value = id;
    document.getElementById('restockProdName').innerText = name;
    document.getElementById('restockModal').classList.remove('hidden');
}

function closeRestockModal() {
    document.getElementById('restockModal').classList.add('hidden');
}

function openAdjustModal(id, name) {
    document.getElementById('adjustProdId').value = id;
    document.getElementById('adjustProdName').innerText = name;
    document.getElementById('adjustModal').classList.remove('hidden');
}

function closeAdjustModal() {
    document.getElementById('adjustModal').classList.add('hidden');
}

function openAddProductModal() {
    document.getElementById('addProductModal').classList.remove('hidden');
}

function closeAddProductModal() {
    document.getElementById('addProductModal').classList.add('hidden');
}
</script>

<?php require __DIR__ . '/layout/foot.php'; ?>

