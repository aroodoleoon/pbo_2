<?php
declare(strict_types=1);

namespace PosClient;

class Config
{
    public const API_BASE_URL = 'http://127.0.0.1:8080/api/v1';
    public const APP_NAME = 'POS Academic Retail Terminal';
    public const APP_VERSION = '2.0.0';
    public const TAX_RATE = 0.11; // PPN 11%

    public static function getApiUrl(string $path): string
    {
        return rtrim(self::API_BASE_URL, '/') . '/' . ltrim($path, '/');
    }
}

