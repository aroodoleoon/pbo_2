<?php
declare(strict_types=1);

/**
 * UI Helper Functions for POS Academic Terminal
 * Replicating the lightweight, high-performance design system from pos-php_v4_api/pos
 */

if (!function_exists('ic')) {
    /**
     * Crisp SVG Icons Helper
     */
    function ic(string $n, int $s = 18): string
    {
        static $P = [
            'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8M12 17.5v-11"/>',
            'box' => '<path d="M21 8 12 3 3 8v8l9 5 9-5Z"/><path d="m3.3 7.5 8.7 5 8.7-5M12 22V12.5"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
            'chart' => '<path d="M3 3v18h18M7 15v3M12 9v9M17 5v13"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
            'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
            'printer' => '<path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'filter' => '<path d="M22 3H2l8 9.5V19l4 2v-8.5Z"/>',
            'userplus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
            'trash' => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
            'edit' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'key' => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 9.8-9.8M17 6l3 3"/>',
            'power' => '<path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>',
            'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8Z"/><circle cx="7" cy="7" r="1"/>',
            'wallet' => '<path d="M20 12V8H6a2 2 0 0 1 0-4h12v4M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
            'alert' => '<path d="m21.7 18-8-14a2 2 0 0 0-3.4 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3ZM12 9v4M12 17h.01"/>',
            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'barcode' => '<path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/>',
            'star' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3-6.2 3.3L7 14.2l-5-4.9 6.9-1Z"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
            'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'cash' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/>',
            'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
            'layers' => '<path d="m12 2 10 5-10 5L2 7Z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/>',
            'percent' => '<path d="m19 5-14 14"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
            'bag' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4ZM3 6h18M16 10a4 4 0 0 1-8 0"/>',
            'card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
            'qr' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3M21 14v7h-4"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
            'reset' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"/>',
            'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
            'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/>',
            'sidebar' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/>',
            'trend' => '<path d="m22 7-8.5 8.5-5-5L2 17M16 7h6v6"/>',
        ];
        return '<svg class="ic" width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($P[$n] ?? '') . '</svg>';
    }
}

if (!function_exists('rp')) {
    /**
     * Format number as Indonesian Rupiah currency
     */
    function rp(float|int $n): string
    {
        return 'Rp ' . number_format((float)$n, 0, ',', '.');
    }
}

if (!function_exists('e')) {
    /**
     * Safe HTML output
     */
    function e(mixed $s): string
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pstat')) {
    /**
     * Product stock status string: 'Habis', 'Menipis', 'Aman'
     */
    function pstat(int $stock, int $min = 10): string
    {
        if ($stock < 1) {
            return 'Habis';
        }
        if ($stock <= $min) {
            return 'Menipis';
        }
        return 'Aman';
    }
}

if (!function_exists('statcard')) {
    /**
     * Metric KPI Stat Card
     */
    function statcard(string $label, string $val, string $sub, string $icon): string
    {
        return '<div class="stat"><div class="ico">' . ic($icon, 20) . '</div><small>' . e($label) . '</small><b>' . $val . '</b><span>' . $sub . '</span></div>';
    }
}
