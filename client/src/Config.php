<?php
declare(strict_types=1);

namespace PosClient;

class Config
{
    public const APP_NAME = 'POS Academic Retail Terminal';
    public const APP_VERSION = '2.0.0';
    public const TAX_RATE = 0.11; // PPN 11%

    public static function getApiBaseUrl(): string
    {
        return getenv('POS_API_BASE') ?: (getenv('API_BASE_URL') ?: 'http://103.119.237.19:3232/api/v1');
    }

    public static function getApiUrl(string $path): string
    {
        return rtrim(self::getApiBaseUrl(), '/') . '/' . ltrim($path, '/');
    }
}
