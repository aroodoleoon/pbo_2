<?php
declare(strict_types=1);

$pageTitle = 'Data Pelanggan & Member';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Customer[] $customers */
/** @var int $totalCustomers */
/** @var int $totalMembers */
/** @var int $totalPoints */
/** @var array $user */

$q = strtolower(trim((string)($_GET['query'] ?? ($_GET['q'] ?? ''))));

$rows = array_filter($customers, function (\PosClient\Models\Customer $c) use ($q) {
    if ($q === '') return true;
    return str_contains(strtolower($c->getName() . $c->getContact() . ($c->getMemberNumber() ?? '')), $q);
});
?>
<div class="bar">
    <p class="mut">Kelola data pelanggan, kartu membership, dan akumulasi poin diskon belanja.</p>
    <div class="acts">
        <button type="button" class="btn" onclick="document.getElementById('mm').showModal()">
            <?= ic('userplus', 16) ?> Daftar member baru
        </button>
    </div>
</div>

<div class="stats">
    <?= statcard('Total pelanggan', count($customers) . ' pelanggan', 'Terdaftar di terminal', 'users') ?>
    <?= statcard('Member loyalitas', $totalMembers . ' member', 'Mendapat benefit diskon belanja', 'tag') ?>
    <?= statcard('Total poin akumulasi', $totalPoints . ' poin', 'Terkumpul dari transaksi', 'star') ?>
</div>

<form class="card filt" method="get" action="">
    <input type="hidden" name="page" value="customers">
    <label class="f grow">
        <span class="lb"><?= ic('search', 15) ?> Cari pelanggan</span>
        <input name="q" value="<?= e($_GET['q'] ?? ($_GET['query'] ?? '')) ?>" placeholder="Nama, no telepon, atau no member">
    </label>
    <button class="btn" type="submit"><?= ic('search', 16) ?> Cari</button>
</form>

<div class="card tbl">
    <table>
        <thead>
            <tr>
                <th>No. member</th>
                <th>Nama pelanggan</th>
                <th><?= ic('phone', 15) ?> No. kontak</th>
                <th>Tier member</th>
                <th>Benefit diskon</th>
                <th>Poin loyalitas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $x): 
                $tier = ucfirst(strtolower($x->getTier()));
                $pct = (int)($x->getDiscountPercent() * 100);
            ?>
                <tr>
                    <td data-l="No"><?= e($x->getMemberNumber() ?? '-') ?></td>
                    <td data-l="Nama"><b><?= e($x->getName()) ?></b></td>
                    <td data-l="Kontak"><?= e($x->getContact()) ?></td>
                    <td data-l="Tier">
                        <span class="pill <?= e($tier) ?>"><?= e($tier) ?></span>
                    </td>
                    <td data-l="Diskon"><?= $pct ?>%</td>
                    <td data-l="Poin"><?= $x->getPoints() ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="6" class="empty">Belum ada pelanggan terdaftar. Daftarkan member baru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Dialog Daftar Member Baru -->
<dialog id="mm">
    <form method="post" action="?page=do-create-customer">
        <h3><?= ic('userplus', 20) ?> Daftar member baru</h3>
        <label class="f">
            Nama pelanggan
            <input name="name" required placeholder="Nama lengkap pelanggan">
        </label>
        <label class="f">
            <?= ic('phone', 15) ?> No. kontak
            <input name="phone" inputmode="tel" required placeholder="08xxxxxxxxxx">
        </label>
        <div class="acts">
            <button type="button" class="btn ghost" onclick="document.getElementById('mm').close()">
                <?= ic('x', 16) ?> Batal
            </button>
            <button class="btn" type="submit">
                <?= ic('userplus', 16) ?> Daftarkan member
            </button>
        </div>
    </form>
</dialog>

<?php require_once __DIR__ . '/layout/foot.php'; ?>
