<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'POS Kasir') ?> · POS Academic</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= !empty($bodyClass) ? e($bodyClass) : '' ?>">
<div class="app" id="app">
<script>
try {
    if (localStorage.getItem("side") === "mini") {
        document.currentScript.parentNode.classList.add("mini");
    }
} catch(e) {}
</script>
