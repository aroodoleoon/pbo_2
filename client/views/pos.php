<?php
declare(strict_types=1);

$pageTitle = 'POS Kasir Terminal';
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/sidebar.php';
require __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Product[] $products */
/** @var array $categories */
/** @var \PosClient\Models\Customer[] $customers */
/** @var array $cartSummary */
/** @var array $user */
?>
<div class="flex flex-col w-full h-[calc(100vh-4rem)] overflow-hidden">
    <div class="flex flex-row w-full h-full overflow-hidden">
        
        <!-- LEFT PANEL: Product Catalog Matrix (65% width) -->
        <div class="w-[65%] flex flex-col h-full bg-surface-container-low/60 overflow-hidden border-r border-outline-variant/20">
            <!-- Top Search & Barcode Scan Bar -->
            <div class="p-space-md pb-space-sm bg-surface-container-lowest flex flex-col gap-space-sm shadow-sm z-10">
                <div class="flex items-center gap-space-md">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-space-md flex items-center pointer-events-none text-primary">
                            <span class="material-symbols-outlined text-[24px]">barcode_scanner</span>
                        </div>
                        <input type="text" id="barcodeSearchInput" autofocus
                               class="w-full pl-12 pr-16 py-space-sm bg-surface-container-low rounded-lg text-on-surface font-body-lg text-body-lg focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary shadow-inner transition-all placeholder:text-outline"
                               placeholder="[ 89999991001 ] Scan barcode atau ketik nama produk (Tekan Enter)..."
                               onkeydown="handleSearchKey(event)"/>
                        <div class="absolute inset-y-0 right-0 pr-space-sm flex items-center gap-space-xs pointer-events-none">
                            <span class="px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm shadow-sm">F2</span>
                        </div>
                    </div>
                    <button type="button" onclick="resetSearch()" class="px-space-md py-space-sm rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors flex items-center gap-space-xs font-label-md text-label-md">
                        <span class="material-symbols-outlined text-[18px]">clear_all</span>
                        <span>Reset</span>
                    </button>
                </div>

                <!-- Category Pills Filter -->
                <div class="flex items-center gap-space-xs overflow-x-auto py-1 no-scrollbar" id="categoryFilterBar">
                    <button type="button" onclick="filterByCategory('all')" 
                            class="cat-pill active px-space-md py-1.5 rounded-full bg-primary-container text-on-primary font-label-md text-label-md flex items-center gap-1.5 shadow-sm whitespace-nowrap" data-cat="all">
                        <span class="material-symbols-outlined text-[16px]">apps</span>
                        <span>Semua (<?= count($products) ?>)</span>
                    </button>
                    <?php foreach ($categories as $cat): ?>
                    <button type="button" onclick="filterByCategory('<?= htmlspecialchars($cat['id']) ?>')" 
                            class="cat-pill px-space-md py-1.5 rounded-full bg-surface-container-lowest text-on-surface-variant hover:text-on-surface hover:bg-surface-container font-label-md text-label-md whitespace-nowrap transition-colors" data-cat="<?= htmlspecialchars($cat['id']) ?>">
                        <span><?= htmlspecialchars($cat['name']) ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product Catalog Matrix Grid -->
            <div class="flex-1 overflow-y-auto p-space-md" id="productGridContainer">
                <div class="grid grid-cols-3 gap-space-md" id="productCardList">
                    <?php if (empty($products)): ?>
                    <div class="col-span-3 py-16 flex flex-col items-center justify-center text-outline gap-2">
                        <span class="material-symbols-outlined text-[48px]">inventory_2</span>
                        <p class="font-medium text-sm">Belum ada barang di database. Silakan jalankan backend dan tambah produk.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                    <div class="product-item group relative flex flex-col justify-between bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer overflow-hidden border border-outline-variant/10 <?= $prod->isLowStock() ? 'ring-1 ring-tertiary-fixed-dim/60' : '' ?>"
                         data-id="<?= htmlspecialchars($prod->getId()) ?>"
                         data-cat="<?= htmlspecialchars($prod->getCategoryId() ?? '') ?>"
                         data-name="<?= htmlspecialchars(strtolower($prod->getName())) ?>"
                         data-barcode="<?= htmlspecialchars($prod->getBarcode()) ?>"
                         onclick="addToCart('<?= htmlspecialchars($prod->getId()) ?>')">
                        
                        <div class="flex items-start justify-between gap-space-xs mb-space-sm">
                            <?php if ($prod->isOutOfStock()): ?>
                            <span class="px-2 py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">block</span>
                                <span>Habis</span>
                            </span>
                            <?php elseif ($prod->isLowStock()): ?>
                            <span class="px-2 py-0.5 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">warning</span>
                                <span>Stok: <?= $prod->getStock() ?></span>
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-semibold">
                                Stok: <?= $prod->getStock() ?>
                            </span>
                            <?php endif; ?>
                            <span class="text-outline text-label-sm font-label-sm tracking-tighter"><?= htmlspecialchars($prod->getBarcode()) ?></span>
                        </div>

                        <!-- Product Visual Illustration -->
                        <div class="relative w-full h-24 rounded-lg overflow-hidden bg-surface-container-high/40 mb-space-sm flex items-center justify-center">
                            <span class="material-symbols-outlined text-[40px] text-primary/70 group-hover:scale-110 transition-transform">
                                <?= str_contains(strtolower($prod->getName()), 'susu') || str_contains(strtolower($prod->getName()), 'teh') || str_contains(strtolower($prod->getName()), 'aqua') ? 'local_cafe' : (str_contains(strtolower($prod->getName()), 'minyak') ? 'oil_barrel' : 'shopping_bag') ?>
                            </span>
                        </div>

                        <div class="flex flex-col">
                            <span class="font-headline-md text-headline-md text-on-surface line-clamp-2 leading-snug font-semibold"><?= htmlspecialchars($prod->getName()) ?></span>
                            <div class="flex items-end justify-between mt-space-sm">
                                <span class="font-numeric-md text-numeric-md text-primary font-bold"><?= $prod->getFormattedSellPrice() ?></span>
                                <button type="button" class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm group-hover:bg-primary transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Action Footer inside Catalog -->
            <div class="px-space-md py-space-sm bg-surface-container-lowest flex items-center justify-between text-on-surface-variant font-label-sm text-label-sm shadow-inner border-t border-outline-variant/20">
                <div class="flex items-center gap-space-md">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-mono font-bold">F2</kbd> Scan / Cari
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-mono font-bold">F9</kbd> Bayar
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-mono font-bold">Esc</kbd> Reset / Tutup
                    </span>
                </div>
                <div class="flex items-center gap-space-xs text-secondary font-label-md text-label-md">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                    <span>Scanner USB & Barcode Ready</span>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Active Order Cart & Bill Summary (35% width) -->
        <div class="w-[35%] flex flex-col h-full bg-surface-container-lowest shadow-xl z-20 overflow-hidden">
            <!-- Cart Header -->
            <div class="p-space-md bg-surface-container-low flex items-center justify-between shadow-sm border-b border-outline-variant/10">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Antrian Kasir Aktif</span>
                    <span class="font-headline-md text-headline-md text-on-surface font-bold tracking-tight">TRX-<?= date('Ymd-His') ?></span>
                </div>
                <button type="button" onclick="clearCart()" title="Batal Transaksi (Esc)"
                        class="flex items-center gap-1 px-space-sm py-1.5 rounded-lg bg-error-container text-error hover:bg-error hover:text-on-error transition-all font-label-sm text-label-sm font-semibold">
                    <span class="material-symbols-outlined text-[16px]">delete_sweep</span>
                    <span>Batal (Esc)</span>
                </button>
            </div>

            <!-- Member Selection Row -->
            <div class="px-space-md py-space-sm bg-surface-container flex items-center justify-between gap-space-sm border-b border-outline-variant/20">
                <div class="flex items-center gap-space-sm min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-full bg-surface-container-highest text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                    </div>
                    <div class="flex flex-col min-w-0 flex-1">
                        <select id="customerSelect" onchange="changeCustomer(this.value)" 
                                class="w-full text-xs font-bold text-on-surface bg-transparent outline-none cursor-pointer">
                            <option value="none">-- Pelanggan Umum (Non-Member) --</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= htmlspecialchars($c->getId()) ?>" <?= (isset($cartSummary['customer']['id']) && $cartSummary['customer']['id'] === $c->getId()) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c->getName()) ?> (<?= $c->getDiscountLabel() ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span id="memberDiscountBadge" class="font-label-sm text-label-sm text-secondary font-semibold">
                            <?= htmlspecialchars($cartSummary['discountLabel'] ?? 'Non-Member') ?>
                        </span>
                    </div>
                </div>
                <a href="?page=customers" class="px-space-sm py-1 rounded bg-surface-container-lowest text-primary hover:bg-primary hover:text-on-primary transition-colors font-label-sm text-label-sm shrink-0 shadow-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">person_add</span>
                    <span>Member Baru</span>
                </a>
            </div>

            <!-- Cart Items Container (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-space-md flex flex-col gap-space-sm" id="cartItemList">
                <?php if (empty($cartSummary['items'])): ?>
                <div id="cartEmptyNotice" class="flex flex-col items-center justify-center h-full text-outline gap-2 py-12">
                    <span class="material-symbols-outlined text-[48px] text-outline/50">shopping_cart</span>
                    <span class="font-body-md text-body-md">Keranjang Kosong. Scan barcode atau klik produk.</span>
                </div>
                <?php else: ?>
                <?php foreach ($cartSummary['items'] as $item): ?>
                <div class="flex flex-col p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container transition-all shadow-sm border border-outline-variant/10">
                    <div class="flex items-start justify-between gap-space-xs">
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="font-body-md text-body-md font-semibold text-on-surface truncate"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="font-label-sm text-label-sm text-outline">@ Rp <?= number_format($item['sellPrice'], 0, ',', '.') ?></span>
                        </div>
                        <button type="button" onclick="removeFromCart('<?= htmlspecialchars($item['id']) ?>')" class="text-outline hover:text-error transition-colors p-0.5">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-space-sm pt-space-xs">
                        <div class="flex items-center bg-surface-container-lowest rounded-lg p-0.5 shadow-inner border border-outline-variant/20">
                            <button type="button" onclick="updateQty('<?= htmlspecialchars($item['id']) ?>', <?= $item['quantity'] - 1 ?>)" class="w-7 h-7 rounded flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-[16px]">remove</span>
                            </button>
                            <span class="w-8 text-center font-numeric-md text-numeric-md font-bold text-on-surface"><?= $item['quantity'] ?></span>
                            <button type="button" onclick="updateQty('<?= htmlspecialchars($item['id']) ?>', <?= $item['quantity'] + 1 ?>)" class="w-7 h-7 rounded flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                            </button>
                        </div>
                        <span class="font-numeric-md text-numeric-md font-bold text-on-surface">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Bill Summary & Calculation Container -->
            <div class="p-space-md bg-surface-container-low flex flex-col gap-space-xs shadow-inner border-t border-outline-variant/20">
                <div class="flex items-center justify-between text-body-md font-body-md text-on-surface-variant">
                    <span>Subtotal (<span id="summaryItemCount"><?= $cartSummary['itemCount'] ?></span> item)</span>
                    <span class="font-semibold text-on-surface" id="summarySubtotal">Rp <?= number_format($cartSummary['subtotal'], 0, ',', '.') ?></span>
                </div>
                <div class="flex items-center justify-between text-body-md font-body-md text-secondary">
                    <span class="flex items-center gap-1 font-semibold">
                        <span class="material-symbols-outlined text-[16px]">loyalty</span>
                        <span id="summaryDiscountLabel">Diskon Member</span>
                    </span>
                    <span class="font-bold" id="summaryDiscountAmount">-Rp <?= number_format($cartSummary['discountAmount'], 0, ',', '.') ?></span>
                </div>
                <div class="flex items-center justify-between text-body-md font-body-md text-on-surface-variant">
                    <span>PPN (11%)</span>
                    <span class="font-semibold text-on-surface" id="summaryTaxAmount">+Rp <?= number_format($cartSummary['taxAmount'], 0, ',', '.') ?></span>
                </div>
                
                <!-- Dashed Visual Line -->
                <div class="my-space-xs border-b-2 border-dashed border-outline-variant/60"></div>
                
                <div class="flex items-baseline justify-between pt-1">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">Total Pembayaran</span>
                        <span class="font-label-md text-label-md text-on-surface-variant">Termasuk Pajak & Diskon</span>
                    </div>
                    <span class="font-numeric-total text-numeric-total text-primary font-extrabold tracking-tight" id="summaryTotal">
                        Rp <?= number_format($cartSummary['total'], 0, ',', '.') ?>
                    </span>
                </div>

                <!-- Big Primary Checkout Action Button -->
                <button type="button" onclick="openPaymentModal()"
                        class="w-full mt-space-sm py-4 px-space-md rounded-xl bg-secondary text-on-secondary font-headline-md text-headline-md font-bold flex items-center justify-between shadow-lg hover:bg-on-secondary-fixed-variant active:scale-[0.99] transition-all group">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[24px]">payments</span>
                        <span>BAYAR SEKARANG (F9)</span>
                    </div>
                    <div class="flex items-center gap-1 bg-surface-container-lowest/20 px-3 py-1 rounded-lg">
                        <span id="btnPayTotal">Rp <?= number_format($cartSummary['total'], 0, ',', '.') ?></span>
                        <span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= PAYMENT MODAL ================= -->
<div class="fixed inset-0 bg-inverse-surface/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="paymentModal">
    <div class="bg-surface-container-lowest rounded-2xl p-space-xl max-w-lg w-full shadow-2xl flex flex-col gap-space-md border border-outline-variant/20 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant/30">
            <div class="flex flex-col">
                <span class="font-headline-lg text-headline-lg font-bold text-on-surface">Proses Pembayaran</span>
                <span class="font-body-md text-body-md text-outline">Pilih metode & konfirmasi nominal bayar</span>
            </div>
            <button type="button" onclick="closePaymentModal()" class="w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div class="bg-surface-container-low p-space-md rounded-xl flex items-center justify-between">
            <span class="font-body-lg text-body-lg text-on-surface-variant font-medium">Total Tagihan:</span>
            <span class="font-numeric-total text-numeric-total text-primary font-extrabold" id="modalBillTotal">Rp 0</span>
        </div>

        <!-- Payment Method Tabs -->
        <div class="grid grid-cols-3 gap-space-sm" id="methodTabs">
            <button type="button" onclick="selectPayMethod('CASH')" id="tabCash"
                    class="py-space-md px-space-sm rounded-xl bg-primary-container text-on-primary flex flex-col items-center gap-1 shadow-sm transition-all font-bold text-sm">
                <span class="material-symbols-outlined text-[24px]">payments</span>
                <span>Tunai (Cash)</span>
            </button>
            <button type="button" onclick="selectPayMethod('QRIS')" id="tabQris"
                    class="py-space-md px-space-sm rounded-xl bg-surface-container text-on-surface hover:bg-surface-container-high flex flex-col items-center gap-1 transition-all font-bold text-sm">
                <span class="material-symbols-outlined text-[24px]">qr_code_2</span>
                <span>QRIS Dinamis</span>
            </button>
            <button type="button" onclick="selectPayMethod('DEBIT')" id="tabDebit"
                    class="py-space-md px-space-sm rounded-xl bg-surface-container text-on-surface hover:bg-surface-container-high flex flex-col items-center gap-1 transition-all font-bold text-sm">
                <span class="material-symbols-outlined text-[24px]">credit_card</span>
                <span>Debit / EDC</span>
            </button>
        </div>

        <!-- Cash In Section (Active for Cash) -->
        <div id="cashInputSection" class="flex flex-col gap-3">
            <div class="flex flex-col gap-1">
                <label class="font-label-sm text-label-sm text-outline font-semibold uppercase">Uang Diterima (Cash In)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-on-surface font-numeric-md font-bold text-outline">Rp</span>
                    <input type="number" id="cashInputVal" oninput="calculateChange()"
                           class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low rounded-lg text-on-surface font-numeric-md text-numeric-md font-bold focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="0"/>
                </div>
            </div>

            <!-- Quick Cash Buttons -->
            <div class="flex gap-space-xs">
                <button type="button" onclick="setQuickCash('exact')" class="flex-1 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high font-label-md text-label-md font-bold text-on-surface transition-colors">Uang Pas</button>
                <button type="button" onclick="setQuickCash(50000)" class="flex-1 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high font-label-md text-label-md font-bold text-on-surface transition-colors">Rp 50.000</button>
                <button type="button" onclick="setQuickCash(100000)" class="flex-1 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high font-label-md text-label-md font-bold text-on-surface transition-colors">Rp 100.000</button>
            </div>

            <!-- Change Amount Box -->
            <div class="bg-secondary-container/40 p-space-md rounded-xl flex items-center justify-between">
                <span class="font-body-md text-body-md text-on-secondary-container font-semibold">Kembalian:</span>
                <span class="font-numeric-md text-numeric-md text-on-secondary-container font-extrabold" id="cashChangeVal">Rp 0</span>
            </div>
        </div>

        <!-- QRIS Section (Hidden by Default) -->
        <div id="qrisSection" class="hidden flex flex-col items-center justify-center p-4 bg-surface-container-low rounded-xl gap-2">
            <div class="w-40 h-40 bg-white p-2 rounded-lg shadow-sm flex items-center justify-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=POS-ACADEMIC-PAY" alt="QRIS Code" class="w-full h-full object-contain"/>
            </div>
            <span class="text-xs font-semibold text-outline">Scan via GoPay, OVO, BCA, Livin Mandiri</span>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-space-md pt-space-xs">
            <button type="button" onclick="closePaymentModal()" class="w-1/3 py-3 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-bold transition-colors">
                Kembali (Esc)
            </button>
            <button type="button" onclick="submitPayment()" id="btnSubmitPayment" class="w-2/3 py-3 rounded-xl bg-secondary hover:bg-on-secondary-fixed-variant text-on-secondary font-headline-md text-headline-md font-bold shadow-md transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span>Selesaikan Transaksi</span>
            </button>
        </div>
    </div>
</div>

<!-- ================= RECEIPT MODAL ================= -->
<div class="fixed inset-0 bg-inverse-surface/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="receiptModal">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-sm w-full shadow-2xl flex flex-col gap-4 border border-outline-variant/30 animate-in zoom-in-95 duration-150">
        <!-- Receipt Content Ready for Print -->
        <div class="receipt-paper bg-white p-4 rounded-lg font-mono text-xs border border-dashed border-outline-variant/60 text-black flex flex-col gap-2" id="printableReceipt">
            <div class="text-center flex flex-col items-center">
                <span class="font-bold text-sm tracking-wider">POS ACADEMIC RETAIL</span>
                <span class="text-[10px] text-gray-600">Polman Kampus Utama • Bandung</span>
                <span class="text-[10px] text-gray-500">NPWP: 01.234.567.8-901.000</span>
            </div>
            <div class="border-b border-dashed border-gray-400 my-1"></div>
            <div class="flex justify-between text-[11px]">
                <span id="rcptTrxNo">TRX-0000</span>
                <span id="rcptDate"><?= date('d/m/Y H:i') ?></span>
            </div>
            <div class="flex justify-between text-[11px]">
                <span>Kasir: <?= htmlspecialchars($user['name'] ?? 'Kasir') ?></span>
                <span id="rcptCustomer">Umum</span>
            </div>
            <div class="border-b border-dashed border-gray-400 my-1"></div>
            <!-- Items list -->
            <div class="flex flex-col gap-1" id="rcptItemsList"></div>
            <div class="border-b border-dashed border-gray-400 my-1"></div>
            <!-- Totals -->
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span id="rcptSubtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Diskon:</span>
                <span id="rcptDiscount">-Rp 0</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>PPN (11%):</span>
                <span id="rcptTax">+Rp 0</span>
            </div>
            <div class="flex justify-between font-bold text-sm pt-1 border-t border-gray-300">
                <span>TOTAL:</span>
                <span id="rcptTotal">Rp 0</span>
            </div>
            <div class="flex justify-between pt-1">
                <span id="rcptMethod">TUNAI:</span>
                <span id="rcptAmountPaid">Rp 0</span>
            </div>
            <div class="flex justify-between font-bold">
                <span>KEMBALI:</span>
                <span id="rcptChange">Rp 0</span>
            </div>
            <div class="border-b border-dashed border-gray-400 my-2"></div>
            <div class="text-center text-[10px] text-gray-500 flex flex-col">
                <span>Terima Kasih Atas Kunjungan Anda</span>
                <span>Barang yang dibeli tidak dapat ditukar</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="printReceipt()" class="flex-1 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-sm flex items-center justify-center gap-1 shadow-md">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Cetak Struk</span>
            </button>
            <button type="button" onclick="closeReceiptModal()" class="flex-1 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-bold text-sm transition-colors">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>

<script>
// State management
let currentCart = <?= json_encode($cartSummary) ?>;
let selectedPayMethod = 'CASH';

function formatRupiah(num) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

// Update UI elements with currentCart
function updateCartUI(cart) {
    currentCart = cart;
    const itemList = document.getElementById('cartItemList');
    const emptyNotice = document.getElementById('cartEmptyNotice');

    document.getElementById('summaryItemCount').innerText = cart.itemCount;
    document.getElementById('summarySubtotal').innerText = formatRupiah(cart.subtotal);
    document.getElementById('summaryDiscountAmount').innerText = '-' + formatRupiah(cart.discountAmount);
    document.getElementById('summaryDiscountLabel').innerText = cart.discountLabel || 'Diskon Member';
    document.getElementById('summaryTaxAmount').innerText = '+' + formatRupiah(cart.taxAmount);
    document.getElementById('summaryTotal').innerText = formatRupiah(cart.total);
    document.getElementById('btnPayTotal').innerText = formatRupiah(cart.total);
    document.getElementById('memberDiscountBadge').innerText = cart.discountLabel || 'Non-Member';

    if (!cart.items || cart.items.length === 0) {
        itemList.innerHTML = `
            <div id="cartEmptyNotice" class="flex flex-col items-center justify-center h-full text-outline gap-2 py-12">
                <span class="material-symbols-outlined text-[48px] text-outline/50">shopping_cart</span>
                <span class="font-body-md text-body-md">Keranjang Kosong. Scan barcode atau klik produk.</span>
            </div>
        `;
        return;
    }

    let html = '';
    cart.items.forEach(item => {
        html += `
            <div class="flex flex-col p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container transition-all shadow-sm border border-outline-variant/10 animate-in fade-in">
                <div class="flex items-start justify-between gap-space-xs">
                    <div class="flex flex-col min-w-0 flex-1">
                        <span class="font-body-md text-body-md font-semibold text-on-surface truncate">${item.name}</span>
                        <span class="font-label-sm text-label-sm text-outline">@ ${formatRupiah(item.sellPrice)}</span>
                    </div>
                    <button type="button" onclick="removeFromCart('${item.id}')" class="text-outline hover:text-error transition-colors p-0.5">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-space-sm pt-space-xs">
                    <div class="flex items-center bg-surface-container-lowest rounded-lg p-0.5 shadow-inner border border-outline-variant/20">
                        <button type="button" onclick="updateQty('${item.id}', ${item.quantity - 1})" class="w-7 h-7 rounded flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[16px]">remove</span>
                        </button>
                        <span class="w-8 text-center font-numeric-md text-numeric-md font-bold text-on-surface">${item.quantity}</span>
                        <button type="button" onclick="updateQty('${item.id}', ${item.quantity + 1})" class="w-7 h-7 rounded flex items-center justify-center text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                        </button>
                    </div>
                    <span class="font-numeric-md text-numeric-md font-bold text-on-surface">${formatRupiah(item.subtotal)}</span>
                </div>
            </div>
        `;
    });
    itemList.innerHTML = html;
}

// AJAX API calls
async function addToCart(productId, qty = 1) {
    try {
        const resp = await fetch('?page=api-add-cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId, quantity: qty })
        });
        const res = await resp.json();
        if (res.success) {
            updateCartUI(res.cart);
        } else {
            alert(res.message || 'Gagal menambahkan barang');
        }
    } catch (e) {
        console.error(e);
    }
}

async function updateQty(productId, qty) {
    try {
        const resp = await fetch('?page=api-update-cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId, quantity: qty })
        });
        const res = await resp.json();
        if (res.success) {
            updateCartUI(res.cart);
        }
    } catch (e) {
        console.error(e);
    }
}

async function removeFromCart(productId) {
    try {
        const resp = await fetch('?page=api-remove-cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId })
        });
        const res = await resp.json();
        if (res.success) {
            updateCartUI(res.cart);
        }
    } catch (e) {
        console.error(e);
    }
}

async function clearCart() {
    if (!confirm('Kosongkan keranjang belanja kasir?')) return;
    try {
        const resp = await fetch('?page=api-clear-cart', { method: 'POST' });
        const res = await resp.json();
        if (res.success) {
            updateCartUI(res.cart);
        }
    } catch (e) {
        console.error(e);
    }
}

async function changeCustomer(customerId) {
    try {
        const resp = await fetch('?page=api-select-customer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ customerId })
        });
        const res = await resp.json();
        if (res.success) {
            updateCartUI(res.cart);
        }
    } catch (e) {
        console.error(e);
    }
}

// Barcode & Product search
function handleSearchKey(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = e.target.value.trim().toLowerCase();
        if (!query) return;

        // Cari di kartu yang ada di DOM terlebih dahulu
        const cards = document.querySelectorAll('.product-item');
        let matched = null;
        for (let card of cards) {
            const barcode = card.dataset.barcode.toLowerCase();
            const name = card.dataset.name.toLowerCase();
            if (barcode === query || name.includes(query)) {
                matched = card;
                break;
            }
        }

        if (matched) {
            addToCart(matched.dataset.id);
            e.target.value = '';
        } else {
            alert(`Produk dengan barcode atau nama "${query}" tidak ditemukan.`);
        }
    }
}

function resetSearch() {
    const input = document.getElementById('barcodeSearchInput');
    input.value = '';
    filterByCategory('all');
    input.focus();
}

function filterByCategory(catId) {
    const pills = document.querySelectorAll('.cat-pill');
    pills.forEach(p => {
        if (p.dataset.cat === catId) {
            p.className = 'cat-pill active px-space-md py-1.5 rounded-full bg-primary-container text-on-primary font-label-md text-label-md flex items-center gap-1.5 shadow-sm whitespace-nowrap';
        } else {
            p.className = 'cat-pill px-space-md py-1.5 rounded-full bg-surface-container-lowest text-on-surface-variant hover:text-on-surface hover:bg-surface-container font-label-md text-label-md whitespace-nowrap transition-colors';
        }
    });

    const cards = document.querySelectorAll('.product-item');
    cards.forEach(card => {
        if (catId === 'all' || card.dataset.cat === catId) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

// Payment modal interactions
function openPaymentModal() {
    if (!currentCart || currentCart.itemCount === 0) {
        alert('Keranjang belanja masih kosong! Silakan pilih barang terlebih dahulu.');
        return;
    }
    document.getElementById('modalBillTotal').innerText = formatRupiah(currentCart.total);
    selectPayMethod('CASH');
    setQuickCash('exact');
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function selectPayMethod(method) {
    selectedPayMethod = method;
    const tabs = {
        CASH: document.getElementById('tabCash'),
        QRIS: document.getElementById('tabQris'),
        DEBIT: document.getElementById('tabDebit')
    };

    for (let k in tabs) {
        if (k === method) {
            tabs[k].className = 'py-space-md px-space-sm rounded-xl bg-primary-container text-on-primary flex flex-col items-center gap-1 shadow-sm transition-all font-bold text-sm';
        } else {
            tabs[k].className = 'py-space-md px-space-sm rounded-xl bg-surface-container text-on-surface hover:bg-surface-container-high flex flex-col items-center gap-1 transition-all font-bold text-sm';
        }
    }

    const cashSec = document.getElementById('cashInputSection');
    const qrisSec = document.getElementById('qrisSection');

    if (method === 'CASH') {
        cashSec.classList.remove('hidden');
        qrisSec.classList.add('hidden');
    } else if (method === 'QRIS') {
        cashSec.classList.add('hidden');
        qrisSec.classList.remove('hidden');
    } else {
        cashSec.classList.add('hidden');
        qrisSec.classList.add('hidden');
    }
}

function setQuickCash(val) {
    const input = document.getElementById('cashInputVal');
    if (val === 'exact') {
        input.value = currentCart.total;
    } else {
        input.value = val;
    }
    calculateChange();
}

function calculateChange() {
    const cashVal = parseInt(document.getElementById('cashInputVal').value || '0', 10);
    const change = Math.max(0, cashVal - currentCart.total);
    document.getElementById('cashChangeVal').innerText = formatRupiah(change);
}

async function submitPayment() {
    const btn = document.getElementById('btnSubmitPayment');
    btn.disabled = true;
    btn.innerText = 'Memproses...';

    const amountPaid = (selectedPayMethod === 'CASH') 
        ? parseInt(document.getElementById('cashInputVal').value || '0', 10) 
        : currentCart.total;

    if (selectedPayMethod === 'CASH' && amountPaid < currentCart.total) {
        alert('Uang yang diberikan kurang dari total tagihan!');
        btn.disabled = false;
        btn.innerText = 'Selesaikan Transaksi';
        return;
    }

    try {
        const resp = await fetch('?page=api-checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                method: selectedPayMethod,
                amountPaid: amountPaid
            })
        });
        const res = await resp.json();

        if (res.success) {
            closePaymentModal();
            showReceipt(res.sale, res.payment);
        } else {
            alert(res.message || 'Gagal memproses pembayaran');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[20px]">check_circle</span><span>Selesaikan Transaksi</span>';
    }
}

// Receipt modal & Print
function showReceipt(sale, payment) {
    document.getElementById('rcptTrxNo').innerText = sale.transactionNumber || 'TRX-POS';
    document.getElementById('rcptDate').innerText = new Date().toLocaleString('id-ID');
    document.getElementById('rcptCustomer').innerText = sale.customerId ? 'Member' : 'Umum';
    
    const subtotal = payment.subtotal || sale.subtotal || 0;
    const discount = payment.discountAmount || sale.discount || 0;
    const tax = sale.tax || 0;
    const total = payment.total || sale.total || (subtotal - discount + tax);
    const amountPaid = payment.amountPaid || total;
    const change = (payment.changeAmount !== undefined && payment.changeAmount !== null && payment.changeAmount < amountPaid) 
        ? payment.changeAmount 
        : Math.max(0, amountPaid - total);

    document.getElementById('rcptSubtotal').innerText = formatRupiah(subtotal);
    document.getElementById('rcptDiscount').innerText = '-' + formatRupiah(discount);
    document.getElementById('rcptTax').innerText = '+' + formatRupiah(tax);
    document.getElementById('rcptTotal').innerText = formatRupiah(total);
    document.getElementById('rcptMethod').innerText = (payment.method || 'CASH') + ':';
    document.getElementById('rcptAmountPaid').innerText = formatRupiah(amountPaid);
    document.getElementById('rcptChange').innerText = formatRupiah(change);

    const itemsBox = document.getElementById('rcptItemsList');
    let itemsHtml = '';
    (sale.items || []).forEach(it => {
        const itemSubtotal = it.subtotal || (it.unitPrice * it.quantity);
        itemsHtml += `
            <div class="flex justify-between" style="display:flex;justify-content:space-between;margin-bottom:2px;">
                <span>${it.productName || 'Barang'} x${it.quantity}</span>
                <span>${formatRupiah(itemSubtotal)}</span>
            </div>
        `;
    });
    itemsBox.innerHTML = itemsHtml;

    document.getElementById('receiptModal').classList.remove('hidden');
    // Refresh cart UI
    updateCartUI({ itemCount: 0, subtotal: 0, discountAmount: 0, taxAmount: 0, total: 0, items: [] });
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
    location.reload();
}

/**
 * Mencetak BENAR-BENAR HANYA lembar struk fisik (Thermal Receipt 80mm/58mm),
 * tanpa elemen antarmuka web, sidebar, header, ataupun tombol modal.
 */
function printReceipt() {
    const receiptBox = document.getElementById('printableReceipt');
    if (!receiptBox) return;

    let iframe = document.getElementById('thermalReceiptFrame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'thermalReceiptFrame';
        iframe.style.position = 'fixed';
        iframe.style.top = '-9999px';
        iframe.style.left = '-9999px';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);
    }

    const frameDoc = iframe.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8"/>
            <title>Cetak Struk Belanja</title>
            <style>
                @page {
                    size: 80mm auto;
                    margin: 0;
                }
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                body {
                    width: 78mm;
                    margin: 0 auto;
                    padding: 6mm 4mm;
                    font-family: 'Courier New', Courier, monospace, monospace;
                    font-size: 11px;
                    line-height: 1.35;
                    color: #000000;
                    background: #ffffff;
                    -webkit-print-color-adjust: exact;
                }
                .text-center { text-align: center; }
                .font-bold { font-weight: bold; }
                .flex { display: flex; }
                .justify-between { display: flex; justify-content: space-between; align-items: baseline; }
                .flex-col { display: flex; flex-direction: column; }
                .items-center { align-items: center; }
                .border-dashed { border-style: dashed; }
                .border-b { border-bottom: 1px dashed #444; }
                .border-t { border-top: 1px solid #000; }
                .my-1 { margin-top: 4px; margin-bottom: 4px; }
                .my-2 { margin-top: 6px; margin-bottom: 6px; }
                .pt-1 { padding-top: 4px; }
                .text-sm { font-size: 13px; }
                .text-xs { font-size: 10px; }
                .text-gray-600, .text-gray-500 { color: #333; }
                .tracking-wider { letter-spacing: 0.05em; }
            </style>
        </head>
        <body>
            ${receiptBox.innerHTML}
        </body>
        </html>
    `);
    frameDoc.close();

    // Berikan sedikit jeda rendering DOM sebelum memanggil dialog print native
    setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }, 250);
}

// Keyboard shortcuts
window.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('barcodeSearchInput').focus();
    } else if (e.key === 'F9') {
        e.preventDefault();
        openPaymentModal();
    } else if (e.key === 'Escape') {
        closePaymentModal();
        closeReceiptModal();
    }
});
</script>

<?php require __DIR__ . '/layout/foot.php'; ?>

