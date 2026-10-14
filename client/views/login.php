<?php
declare(strict_types=1);

$pageTitle = 'Login Petugas Kasir';
require __DIR__ . '/layout/head.php';
$flash = \PosClient\Session::getFlash();
?>
<div class="min-h-screen w-full flex items-center justify-center p-6 bg-surface relative overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-primary-container/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-secondary-container/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full bg-surface-container-lowest rounded-2xl shadow-xl p-8 border border-outline-variant/30 flex flex-col gap-6 relative z-10">
        <!-- Brand Header -->
        <div class="flex flex-col items-center text-center gap-2">
            <div class="w-14 h-14 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center shadow-md">
                <span class="material-symbols-outlined text-[32px]">point_of_sale</span>
            </div>
            <h1 class="text-2xl font-bold text-on-surface tracking-tight mt-2">POS Academic Terminal</h1>
            <p class="text-sm text-on-surface-variant">Sistem Kasir & Manajemen Ritel Terintegrasi</p>
        </div>

        <?php if ($flash): ?>
        <div class="p-3.5 rounded-xl text-sm font-medium flex items-center gap-2 <?= $flash['type'] === 'error' ? 'bg-error-container text-on-error-container' : 'bg-surface-container text-on-surface' ?>">
            <span class="material-symbols-outlined text-[20px] text-error">error</span>
            <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="?page=do-login" method="POST" class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-outline uppercase tracking-wider">Username</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3 text-outline text-[20px]">person</span>
                    <input type="text" name="username" id="usernameInput" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low rounded-xl border border-transparent focus:border-primary focus:bg-surface-container-lowest focus:outline-none transition-all text-on-surface text-sm font-medium" 
                           placeholder="admin atau kasir1"/>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-outline uppercase tracking-wider">Password</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3 text-outline text-[20px]">lock</span>
                    <input type="password" name="password" id="passwordInput" required
                           class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low rounded-xl border border-transparent focus:border-primary focus:bg-surface-container-lowest focus:outline-none transition-all text-on-surface text-sm font-medium" 
                           placeholder="••••••••"/>
                </div>
            </div>

            <button type="submit" class="mt-2 w-full py-3 px-4 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-md hover:bg-primary-container transition-all flex items-center justify-center gap-2">
                <span>Masuk Terminal</span>
                <span class="material-symbols-outlined text-[18px]">login</span>
            </button>
        </form>

        <!-- Quick Demo Credentials Picker -->
        <div class="border-t border-outline-variant/30 pt-4 flex flex-col gap-2">
            <span class="text-xs text-outline font-semibold uppercase tracking-wider text-center">Akun Cepat (Development Demo)</span>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillCreds('admin', 'admin123')"
                        class="p-2 rounded-lg bg-surface-container hover:bg-surface-container-high transition-colors text-left flex flex-col">
                    <span class="text-xs font-bold text-on-surface">Admin Utama</span>
                    <span class="text-[11px] text-outline">admin / admin123</span>
                </button>
                <button type="button" onclick="fillCreds('kasir1', 'kasir123')"
                        class="p-2 rounded-lg bg-surface-container hover:bg-surface-container-high transition-colors text-left flex flex-col">
                    <span class="text-xs font-bold text-on-surface">Kasir Shift 1</span>
                    <span class="text-[11px] text-outline">kasir1 / kasir123</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(u, p) {
    document.getElementById('usernameInput').value = u;
    document.getElementById('passwordInput').value = p;
}
</script>
</body>
</html>
