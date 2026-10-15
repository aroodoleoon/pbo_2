<?php
declare(strict_types=1);

$pageTitle = 'POS Kasir';
$bodyClass = 'wide';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Product[] $products */
/** @var array $categories */
/** @var \PosClient\Models\Customer[] $customers */
/** @var array $cartSummary */
/** @var array $user */

$productData = array_map(function (\PosClient\Models\Product $p) {
    return [
        'id' => $p->getId(),
        'sku' => $p->getBarcode(),
        'name' => $p->getName(),
        'buy' => $p->getBuyPrice(),
        'sell' => $p->getSellPrice(),
        'stock' => $p->getStock(),
        'min' => $p->getMinStock(),
        'cat' => $p->getCategoryId(),
    ];
}, $products);
?>
<div class="pos">
    <div class="pos-l">
        <div class="panel" id="p-produk">
            <div class="scan">
                <div class="sbox">
                    <?= ic('barcode', 20) ?>
                    <input id="q" placeholder="Scan barcode atau ketik nama produk, lalu Enter (F2)" autofocus autocomplete="off">
                </div>
                <button class="btn ghost" onclick="resetSearch()"><?= ic('reset', 16) ?> Reset</button>
            </div>

            <div id="grid" class="grid"></div>
            <p class="keys mut"><kbd>F2</kbd> Scan/cari &nbsp; <kbd>F9</kbd> Bayar &nbsp; <kbd>Esc</kbd> Batalkan</p>
        </div>

        <!-- Panel Katalog Barang (dibuka dari sidebar) -->
        <div class="panel" id="p-katalog" hidden>
            <?php $panel = true; require __DIR__ . '/partials/catalog.php'; ?>
        </div>

        <!-- Panel Data Pelanggan (dibuka dari sidebar) -->
        <div class="panel" id="p-member" hidden>
            <?php $panel = true; require __DIR__ . '/partials/members.php'; ?>
        </div>
    </div>

    <aside class="cart">
        <div class="cart-h">
            <div>
                <small class="mut">Antrian aktif</small>
                <b id="activeTrxNo"><?= 'TRX-' . date('Ymd-His') ?></b>
            </div>
            <button class="btn danger" onclick="clr()"><?= ic('x', 16) ?> Batal</button>
        </div>

        <label class="f">
            <span class="lb"><?= ic('user', 15) ?> Pelanggan</span>
            <select id="cust" onchange="calc()">
                <option value="0" data-p="0">Pelanggan umum (non-member)</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= e($c->getId()) ?>" data-p="<?= (int)($c->getDiscountPercent() * 100) ?>">
                        <?= e($c->getName()) ?> · <?= e($c->getTier()) ?> (<?= (int)($c->getDiscountPercent() * 100) ?>%)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button class="btn ghost wide" onclick="document.getElementById('mm').showModal()"><?= ic('userplus', 16) ?> Member baru</button>

        <div id="lines" class="lines"></div>

        <dl class="sum">
            <dt>Subtotal</dt>
            <dd id="s1">Rp 0</dd>
            <dt><?= ic('percent', 14) ?> Diskon member</dt>
            <dd id="s2">-Rp 0</dd>
            <dt>PPN 11%</dt>
            <dd id="s3">Rp 0</dd>
        </dl>

        <div class="total">
            <span>Total bayar</span>
            <b id="s4">Rp 0</b>
        </div>

        <div class="seg sm">
            <label>
                <input type="radio" name="m" value="CASH" checked>
                <span><?= ic('cash', 16) ?> Tunai</span>
            </label>
            <label>
                <input type="radio" name="m" value="QRIS">
                <span><?= ic('qr', 16) ?> QRIS</span>
            </label>
            <label>
                <input type="radio" name="m" value="DEBIT">
                <span><?= ic('card', 16) ?> Debit</span>
            </label>
        </div>

        <button class="btn big pay" onclick="pay()"><?= ic('check', 20) ?> Bayar sekarang (F9)</button>
    </aside>
</div>

<!-- Modal Konfirmasi Pembayaran -->
<dialog id="pd">
    <h3><?= ic('wallet', 20) ?> Konfirmasi pembayaran</h3>
    <p class="mut" id="pdno"></p>
    <dl class="sum" id="pdsum"></dl>
    <div class="total">
        <span>Total bayar</span>
        <b id="pdt">Rp 0</b>
    </div>
    <label class="f" id="pdcash">
        <span class="lb"><?= ic('cash', 15) ?> Uang diterima</span>
        <input id="pdin" type="number" min="0" inputmode="numeric" placeholder="Masukkan nominal uang tunai">
    </label>
    <p id="pdchg" class="mut"></p>
    <div class="acts">
        <button type="button" class="btn ghost" id="pdx"><?= ic('x', 16) ?> Batal</button>
        <button type="button" class="btn" id="pdok"><?= ic('check', 16) ?> Konfirmasi bayar</button>
    </div>
</dialog>

<!-- Dialog Katalog & Member (dipakai panel dan tombol "Member baru" di keranjang) -->
<?php
$back = '?page=pos#katalog';
require __DIR__ . '/partials/catalog_dialogs.php';
$back = '?page=pos#member';
require __DIR__ . '/partials/member_dialog.php';
?>

<script>
const P = <?= json_encode($productData, JSON_UNESCAPED_UNICODE) ?>;
const $ = id => document.getElementById(id);
const EM = '<p class="empty">Keranjang kosong. Scan barcode atau ketuk produk.</p>';
let cart = {};

const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const TITLES = { produk: 'POS Kasir', katalog: 'Katalog Barang', member: 'Data Pelanggan' };

function tab(t) {
    if (!TITLES[t]) t = 'produk';
    document.querySelectorAll('#side nav a[data-t]').forEach(a => a.classList.toggle('on', a.dataset.t === t));
    document.querySelectorAll('.pos-l .panel').forEach(pn => pn.hidden = pn.id !== 'p-' + t);
    const h = document.querySelector('.top h1');
    if (h) h.textContent = TITLES[t];
    try { history.replaceState(null, '', location.pathname + location.search + '#' + t); } catch (e) {}
    $('side').classList.remove('open');
    if (t === 'produk') $('q').focus();
}

document.querySelectorAll('#side nav a[data-t]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    tab(a.dataset.t);
}));

function resetSearch() {
    $('q').value = '';
    draw();
    $('q').focus();
}

function draw() {
    const t = $('q').value.trim().toLowerCase();
    const filtered = P.filter(p => !t || p.name.toLowerCase().includes(t) || p.sku.toLowerCase().includes(t));
    if (!filtered.length) {
        $('grid').innerHTML = '<p class="empty" style="grid-column: 1 / -1;">Produk tidak ditemukan.</p>';
        return;
    }

    $('grid').innerHTML = filtered.map(p => `
        <button type="button" class="prod" ${p.stock < 1 ? 'disabled' : ''} onclick="add('${esc(p.id)}')">
            <b>${esc(p.name)}</b>
            <small>${esc(p.sku)}</small>
            <span>${rp(p.sell)}</span>
            <em class="${p.stock < 1 ? 'bad' : ''}">${p.stock < 1 ? 'Habis' : 'Stok ' + p.stock}</em>
        </button>
    `).join('');
}

function add(id) {
    const p = P.find(x => String(x.id) === String(id));
    if (!p) return;
    const cur = cart[id] || 0;
    if (cur < p.stock) {
        cart[id] = cur + 1;
        calc();
    } else {
        alert('Stok fisik produk ' + p.name + ' tidak mencukupi (sisa: ' + p.stock + ')');
    }
}

function chg(id, n) {
    const p = P.find(x => String(x.id) === String(id));
    if (!p) return;
    const cur = (cart[id] || 0) + n;
    if (cur <= 0) {
        delete cart[id];
    } else if (cur > p.stock) {
        alert('Maksimal stok tersedia: ' + p.stock);
        cart[id] = p.stock;
    } else {
        cart[id] = cur;
    }
    calc();
}

function clr() {
    cart = {};
    calc();
}

function calc() {
    let s = 0, h = '';
    for (const id in cart) {
        const p = P.find(x => String(x.id) === String(id));
        if (!p) {
            delete cart[id];
            continue;
        }
        const itemTotal = p.sell * cart[id];
        s += itemTotal;
        h += `
        <div class="ln">
            <div>
                <b>${esc(p.name)}</b>
                <small>${rp(p.sell)} × ${cart[id]}</small>
            </div>
            <div class="qty">
                <button type="button" onclick="chg('${esc(id)}', -1)">−</button>
                <span>${cart[id]}</span>
                <button type="button" onclick="chg('${esc(id)}', 1)">+</button>
            </div>
            <b>${rp(itemTotal)}</b>
        </div>`;
    }

    const selOpt = $('cust').selectedOptions[0];
    const pc = +(selOpt ? selOpt.dataset.p || 0 : 0);
    const d = Math.round(s * pc / 100);
    const v = Math.round((s - d) * 0.11);
    const tot = s - d + v;

    $('lines').innerHTML = h || EM;
    $('s1').textContent = rp(s);
    $('s2').textContent = '-' + rp(d);
    $('s3').textContent = rp(v);
    $('s4').textContent = rp(tot);

    try {
        sessionStorage.setItem('pos', JSON.stringify({ cart, cust: $('cust').value }));
    } catch (e) {}
}

let SALE_SUMMARY = null;
let SELECTED_METHOD = 'CASH';

function chgCalc() {
    if (!SALE_SUMMARY) return;
    const p = +$('pdin').value || 0;
    const diff = p - SALE_SUMMARY.total;
    if (p > 0) {
        $('pdchg').textContent = diff >= 0 ? 'Kembalian: ' + rp(diff) : 'Kurang: ' + rp(-diff);
        $('pdchg').style.color = diff >= 0 ? 'var(--p)' : 'var(--bad)';
    } else {
        $('pdchg').textContent = '';
    }
}

function pay() {
    if (document.querySelector('dialog[open]')) return;
    const items = Object.keys(cart).map(id => ({ productId: id, quantity: cart[id] }));
    if (!items.length) {
        alert('Keranjang masih kosong. Pilih atau scan produk terlebih dahulu.');
        return;
    }

    const mRadio = document.querySelector('[name=m]:checked');
    SELECTED_METHOD = mRadio ? mRadio.value : 'CASH';

    let subtotal = 0;
    for (const id in cart) {
        const p = P.find(x => String(x.id) === String(id));
        if (p) subtotal += p.sell * cart[id];
    }
    const pc = +($('cust').selectedOptions[0]?.dataset.p || 0);
    const discount = Math.round(subtotal * pc / 100);
    const tax = Math.round((subtotal - discount) * 0.11);
    const total = subtotal - discount + tax;

    SALE_SUMMARY = {
        no: $('activeTrxNo').textContent,
        subtotal,
        discount,
        tax,
        total,
        method: SELECTED_METHOD,
        items,
        customerId: $('cust').value !== '0' ? $('cust').value : null,
    };

    const methodLabels = { CASH: 'Tunai', QRIS: 'QRIS', DEBIT: 'Debit' };
    $('pdno').textContent = SALE_SUMMARY.no;
    $('pdsum').innerHTML = `
        <dt>Subtotal</dt><dd>${rp(subtotal)}</dd>
        <dt>Diskon member</dt><dd>-${rp(discount)}</dd>
        <dt>PPN 11%</dt><dd>${rp(tax)}</dd>
        <dt>Metode</dt><dd>${methodLabels[SELECTED_METHOD] || SELECTED_METHOD}</dd>
    `;
    $('pdt').textContent = rp(total);
    $('pdcash').hidden = SELECTED_METHOD !== 'CASH';
    $('pdin').value = '';
    $('pdchg').textContent = '';

    $('pd').showModal();
    if (SELECTED_METHOD === 'CASH') {
        $('pdin').focus();
    }
}

async function confirmPay() {
    if (!SALE_SUMMARY) return;
    const paid = SELECTED_METHOD === 'CASH' ? (+$('pdin').value || 0) : SALE_SUMMARY.total;
    if (SELECTED_METHOD === 'CASH' && paid < SALE_SUMMARY.total) {
        alert('Nominal uang tunai yang diterima kurang dari total belanja.');
        $('pdin').focus();
        return;
    }

    const btn = $('pdok');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    try {
        const payload = {
            method: SELECTED_METHOD,
            amountPaid: paid,
            items: SALE_SUMMARY.items,
            customerId: SALE_SUMMARY.customerId,
            discountAmount: SALE_SUMMARY.discount,
        };

        const res = await fetch('?page=api-checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        btn.disabled = false;
        btn.textContent = 'Konfirmasi bayar';

        if (!data.success && !data.ok) {
            alert('Pembayaran gagal: ' + (data.message || data.msg || 'Terjadi kesalahan sistem.'));
            return;
        }

        $('pd').close();
        const chgVal = data.change ?? Math.max(0, paid - SALE_SUMMARY.total);
        alert('Pembayaran Berhasil!\nNo. Transaksi: ' + (data.sale?.id || SALE_SUMMARY.no) + '\nTotal: ' + rp(data.total || SALE_SUMMARY.total) + (SELECTED_METHOD === 'CASH' ? '\nKembalian: ' + rp(chgVal) : ''));
        
        clr();
        location.reload();
    } catch (e) {
        btn.disabled = false;
        btn.textContent = 'Konfirmasi bayar';
        alert('Gagal menghubungi server untuk memproses transaksi.');
    }
}

$('pdin').addEventListener('input', chgCalc);
$('pdin').addEventListener('keydown', e => {
    if (e.key === 'Enter') confirmPay();
});
$('pdok').onclick = confirmPay;
$('pdx').onclick = () => {
    $('pd').close();
    SALE_SUMMARY = null;
};

$('q').addEventListener('input', draw);
$('q').addEventListener('keydown', async e => {
    if (e.key === 'Enter') {
        const val = $('q').value.trim();
        if (!val) return;
        
        const match = P.find(p => p.sku === val);
        if (match) {
            if (match.stock > 0) {
                add(match.id);
                $('q').value = '';
                draw();
            } else {
                alert('Stok fisik produk ' + match.name + ' habis.');
            }
            return;
        }

        // Lookup online ke backend endpoint barcode
        try {
            const res = await fetch('?page=api-barcode-scan&barcode=' + encodeURIComponent(val));
            const json = await res.json();
            if (json.success && json.product) {
                const prod = json.product;
                P.push({
                    id: prod.id,
                    sku: prod.barcode,
                    name: prod.name,
                    buy: prod.buyPrice,
                    sell: prod.sellPrice,
                    stock: prod.stock,
                    min: prod.minStock,
                    cat: prod.categoryId
                });
                add(prod.id);
                $('q').value = '';
                draw();
            } else {
                alert(json.message || 'Barang dengan barcode "' + val + '" tidak ditemukan.');
            }
        } catch (err) {
            console.error('Gagal scan barcode:', err);
        }
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'F2') {
        e.preventDefault();
        tab('produk');
        $('q').focus();
    }
    if (e.key === 'F9') {
        e.preventDefault();
        pay();
    }
    if (e.key === 'Escape' && !document.querySelector('dialog[open]')) {
        clr();
    }
});

try {
    const s = JSON.parse(sessionStorage.getItem('pos') || '{}');
    cart = s.cart || {};
    if (s.cust && $('cust')) $('cust').value = s.cust;
} catch (e) {}

draw();
calc();
tab(location.hash.slice(1) || 'produk');
</script>
<?php require_once __DIR__ . '/layout/foot.php'; ?>
