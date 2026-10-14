<?php
declare(strict_types=1);

/**
 * Fallback router untuk root repository jika PHP Server dijalankan dari root pbo_2.
 * Merekomendasikan menjalankan: php -S localhost:8000 -t client
 */
header('Location: /client/');
exit;
