<?php
declare(strict_types=1);
/** Dialog Daftar Member Baru (#mm). Opsional: $back */
$back = $back ?? '?page=customers';
?>
<dialog id="mm">
    <form method="post" action="?page=do-create-customer">
        <input type="hidden" name="back" value="<?= e($back) ?>">
        <h3><?= ic('userplus', 20) ?> Daftar member baru</h3>
        <label class="f">
            Nama pelanggan
            <input name="name" required placeholder="Nama lengkap pelanggan">
        </label>
        <label class="f">
            <span class="lb"><?= ic('phone', 15) ?> No. kontak</span>
            <input name="phone" inputmode="tel" required placeholder="08xxxxxxxxxx">
        </label>
        <div class="acts">
            <button type="button" class="btn ghost" onclick="document.getElementById('mm').close()"><?= ic('x', 16) ?> Batal</button>
            <button class="btn" type="submit"><?= ic('userplus', 16) ?> Daftarkan member</button>
        </div>
    </form>
</dialog>
