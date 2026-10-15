<?php
declare(strict_types=1);

$pageTitle = 'Data Pelanggan & Member';
require_once __DIR__ . '/layout/head.php';
require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';

/** @var \PosClient\Models\Customer[] $customers */

$panel = false;
require __DIR__ . '/partials/members.php';
$back = '?page=customers';
require __DIR__ . '/partials/member_dialog.php';
require_once __DIR__ . '/layout/foot.php';
