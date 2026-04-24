<?php

namespace App\Core\Helpers;

use Carbon\Carbon;

class FormattingHelper
{
    public static function formatDate(?Carbon $date, string $format = 'd/m/Y'): string
    {
        return $date?->format($format) ?? '-';
    }

    public static function formatDateTime(?Carbon $date, string $format = 'd/m/Y H:i:s'): string
    {
        return $date?->format($format) ?? '-';
    }

    public static function formatTime(?Carbon $date, string $format = 'H:i:s'): string
    {
        return $date?->format($format) ?? '-';
    }

    public static function formatCurrency(float $value, string $currency = 'EUR'): string
    {
        $symbols = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'];
        $symbol = $symbols[$currency] ?? $currency;

        return number_format($value, 2, ',', '.') . ' ' . $symbol;
    }

    public static function formatPercent(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.') . '%';
    }

    public static function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins}m";
    }

    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
