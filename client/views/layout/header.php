<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
$flash = \PosClient\Session::getFlash();
?>
<main>
    <header class="top">
        <button class="burger" onclick="document.getElementById('side').classList.toggle('open')" aria-label="Menu">
            <?= ic('menu', 22) ?>
        </button>
        <button class="collapse" onclick="var a=document.getElementById('app');a.classList.toggle('mini');try{localStorage.setItem('side',a.classList.contains('mini')?'mini':'open')}catch(e){}" aria-label="Perkecil atau perluas menu" title="Perkecil / perluas menu">
            <?= ic('sidebar', 20) ?>
        </button>
        <h1><?= e($pageTitle ?? 'POS Kasir') ?></h1>
        <span class="date"><?= ic('calendar', 15) ?><?= date('d/m/Y') ?></span>
    </header>
    <section class="body">
        <?php if ($flash): ?>
            <div class="note <?= $flash['type'] === 'error' ? 'bad' : '' ?>">
                <?= ic($flash['type'] === 'error' ? 'alert' : 'check', 16) ?>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>
