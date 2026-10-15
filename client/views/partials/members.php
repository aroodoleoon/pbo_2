<?php
declare(strict_types=1);

/**
 * Isi halaman Data Pelanggan (dipakai halaman manager dan panel di halaman kasir).
 * Variabel: $customers. Opsional: $panel. Dialog #mm disertakan oleh halaman pemanggil.
 */
$panel = $panel ?? false;
$mq = $panel ? 'mq' : 'q';
$q = strtolower(trim((string)($_GET[$mq] ?? ($panel ? '' : ($_GET['query'] ?? '')))));

$totalMembers = 0;
$totalPoints = 0;
foreach ($customers as $c) {
    if ($c->isMember()) {
        $totalMembers++;
    }
    $totalPoints += $c->getPoints();
}

$rows = array_filter($customers, function (\PosClient\Models\Customer $c) use ($q) {
    if ($q === '') {
        return true;
    }
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

<div class="stats three">
    <?= statcard('Total pelanggan', count($customers) . ' pelanggan', 'Terdaftar di terminal', 'users') ?>
    <?= statcard('Member loyalitas', $totalMembers . ' member', 'Mendapat benefit diskon belanja', 'tag') ?>
    <?= statcard('Total poin akumulasi', $totalPoints . ' poin', 'Terkumpul dari transaksi', 'star') ?>
</div>

<form class="card filt" method="get" action="<?= $panel ? '?#member' : '' ?>">
    <input type="hidden" name="page" value="<?= $panel ? 'pos' : 'customers' ?>">
    <label class="f grow">
        <span class="lb"><?= ic('search', 15) ?> Cari pelanggan</span>
        <input name="<?= $mq ?>" value="<?= e($_GET[$mq] ?? ($panel ? '' : ($_GET['query'] ?? ''))) ?>" placeholder="Nama, no telepon, atau no member">
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
                    <td data-l="Tier"><span class="pill <?= e($tier) ?>"><?= e($tier) ?></span></td>
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
