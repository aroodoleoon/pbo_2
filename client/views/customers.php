<?php
declare(strict_types=1);

$pageTitle = 'Data Pelanggan & Member';
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/sidebar.php';
require __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Customer[] $customers */
/** @var int $totalCustomers */
/** @var int $totalMembers */
/** @var int $totalPoints */
/** @var array $user */
?>
<div class="px-gutter-lg py-space-xl flex flex-col gap-space-xl">
    <!-- Top Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg">
        <div class="flex flex-col">
            <div class="flex items-center gap-space-sm mb-space-xs">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-container text-on-primary text-body-sm font-bold">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                </span>
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold">Modul Pelanggan & Member (Dhia)</span>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface font-bold tracking-tight">Data Pelanggan & Program Loyalitas Member</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Kelola data pelanggan, kartu membership, dan akumulasi poin diskon belanja.</p>
        </div>
        <button type="button" onclick="openAddMemberModal()"
                class="inline-flex items-center gap-space-sm px-space-lg py-space-sm rounded-lg bg-primary-container text-on-primary font-body-md text-body-md font-semibold hover:bg-primary transition-all shadow-md">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            <span>Daftar Member Baru</span>
        </button>
    </div>

    <!-- 3 Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-lg">
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs uppercase text-outline font-bold tracking-wider">Total Pelanggan</span>
                <span class="font-numeric-total text-on-surface font-extrabold"><?= $totalCustomers ?></span>
                <span class="text-xs text-secondary font-medium">Aktif bertransaksi</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[26px]">groups</span>
            </div>
        </div>

        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs uppercase text-outline font-bold tracking-wider">Member Loyalitas</span>
                <span class="font-numeric-total text-on-surface font-extrabold"><?= $totalMembers ?></span>
                <span class="text-xs text-secondary font-medium">Mendapat diskon bertingkat</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-secondary-container/40 flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-[26px]">loyalty</span>
            </div>
        </div>

        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/10 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs uppercase text-outline font-bold tracking-wider">Total Poin Akumulasi</span>
                <span class="font-numeric-total text-on-surface font-extrabold"><?= number_format($totalPoints, 0, ',', '.') ?></span>
                <span class="text-xs text-primary font-medium">Bisa ditukar diskon</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-tertiary-fixed/30 flex items-center justify-center text-tertiary">
                <span class="material-symbols-outlined text-[26px]">stars</span>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/20 text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                        <th class="py-3.5 px-4">No. Member</th>
                        <th class="py-3.5 px-4">Nama Pelanggan</th>
                        <th class="py-3.5 px-4">No. Kontak</th>
                        <th class="py-3.5 px-4 text-center">Tier Member</th>
                        <th class="py-3.5 px-4 text-center">Benefit Diskon</th>
                        <th class="py-3.5 px-4 text-center">Poin Loyalitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-sm">
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-outline">
                            Belum ada pelanggan terdaftar.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr class="hover:bg-surface-container-low/40 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-primary">
                            <?= htmlspecialchars($c->getMemberNumber() ?? 'NON-MEMBER') ?>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-on-surface">
                            <?= htmlspecialchars($c->getName()) ?>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-on-surface-variant">
                            <?= htmlspecialchars($c->getContact()) ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if ($c->isMember()): ?>
                            <?php 
                                $t = $c->getTier(); 
                                $tierColor = match($t) {
                                    'PLATINUM' => 'bg-purple-100 text-purple-800 border-purple-300',
                                    'GOLD' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    'SILVER' => 'bg-slate-200 text-slate-800 border-slate-300',
                                    default => 'bg-blue-100 text-blue-800 border-blue-300'
                                };
                            ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $tierColor ?>">
                                <span class="material-symbols-outlined text-[13px]">verified</span>
                                <span><?= $t ?></span>
                            </span>
                            <?php else: ?>
                            <span class="text-xs text-outline font-semibold">Pelanggan Umum</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-secondary text-sm">
                                <?= (int)($c->getDiscountPercent() * 100) ?>%
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 font-numeric-md font-bold text-primary">
                                <span class="material-symbols-outlined text-[16px]">stars</span>
                                <span><?= $c->getPoints() ?></span>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH MEMBER ================= -->
<div class="fixed inset-0 bg-inverse-surface/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4" id="addMemberModal">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-md w-full shadow-2xl border border-outline-variant/30 flex flex-col gap-4 animate-in zoom-in-95">
        <div class="flex items-center justify-between border-b border-outline-variant/20 pb-3">
            <span class="font-headline-md font-bold text-on-surface">Registrasi Pelanggan & Member</span>
            <button type="button" onclick="closeAddMemberModal()" class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="?page=do-create-customer" method="POST" class="flex flex-col gap-3">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary" placeholder="Dewi Sartika"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold uppercase text-outline">Nomor Telepon / Kontak WhatsApp</label>
                <input type="text" name="contact" required class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-on-surface font-mono text-sm outline-none focus:ring-2 focus:ring-primary" placeholder="081234567890"/>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-lg bg-surface-container-low mt-1">
                <input type="checkbox" name="asMember" id="asMemberCheck" checked class="w-4 h-4 text-primary rounded outline-none"/>
                <label for="asMemberCheck" class="text-xs font-semibold text-on-surface cursor-pointer">
                    Daftarkan langsung sebagai Member (Dapatkan kartu & diskon)
                </label>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeAddMemberModal()" class="w-1/3 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-bold">Batal</button>
                <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-xs shadow-md">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddMemberModal() {
    document.getElementById('addMemberModal').classList.remove('hidden');
}
function closeAddMemberModal() {
    document.getElementById('addMemberModal').classList.add('hidden');
}
</script>

<?php require __DIR__ . '/layout/foot.php'; ?>

