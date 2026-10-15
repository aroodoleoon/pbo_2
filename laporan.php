<?php
declare(strict_types=1);
header('Location: /client/laporan.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit;
