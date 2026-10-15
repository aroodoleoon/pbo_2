<?php
declare(strict_types=1);

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = '/client/' . ($query !== '' ? '?' . $query : '');
header('Location: ' . $target);
exit;
