<?php
declare(strict_types=1);

require_once __DIR__ . '/layout/helpers.php';
$flash = \PosClient\Session::getFlash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · POS Academic</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login">
    <div class="lg-art">
        <h2>Kasir cepat,<br>laporan jelas.</h2>
        <p>Satu sistem untuk transaksi di kasir dan kendali stok, pelanggan, serta laporan untuk manager.</p>
    </div>

    <form class="lg-card" method="post" action="?page=do-login">
        <h1>Masuk ke POS</h1>
        <p class="mut">Pilih peran Anda, lalu masukkan akun.</p>

        <div class="seg">
            <label>
                <input type="radio" name="role" value="kasir" checked>
                <span><?= ic('receipt', 18) ?> Kasir</span>
            </label>
            <label>
                <input type="radio" name="role" value="manager">
                <span><?= ic('chart', 18) ?> Manager</span>
            </label>
        </div>

        <?php if ($flash): ?>
            <div class="note <?= $flash['type'] === 'error' ? 'bad' : '' ?>">
                <?= ic($flash['type'] === 'error' ? 'alert' : 'check', 16) ?>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <label class="f">
            <span class="lb"><?= ic('user', 15) ?> Username</span>
            <input name="username" id="un" required autofocus autocomplete="username" placeholder="Masukkan username">
        </label>

        <label class="f">
            <span class="lb"><?= ic('lock', 15) ?> Password</span>
            <input name="password" id="pw" type="password" required autocomplete="current-password" placeholder="••••••••">
        </label>

        <button class="btn big" type="submit">
            <?= ic('login', 20) ?> Masuk
        </button>

        <div class="demo">
            <p class="mut">Akun demo backend (klik untuk mengisi)</p>
            <div>
                <button type="button" onclick="fill('admin', 'admin123', 'manager')">
                    Manager
                    <small>admin / admin123</small>
                </button>
                <button type="button" onclick="fill('kasir1', 'kasir123', 'kasir')">
                    Kasir
                    <small>kasir1 / kasir123</small>
                </button>
            </div>
            <p class="mut" style="margin-top:.6rem">Akun kasir mengikuti data di backend.</p>
        </div>
    </form>

    <script>
    function fill(u, p, r) {
        document.getElementById('un').value = u;
        document.getElementById('pw').value = p;
        const radio = document.querySelector('[name=role][value=' + r + ']');
        if (radio) radio.checked = true;
    }
    </script>
</body>
</html>
