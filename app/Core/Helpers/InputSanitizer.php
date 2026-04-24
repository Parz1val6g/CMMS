<?php

namespace App\Core\Helpers;

class InputSanitizer
{
    public static function sanitize(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    public static function sanitizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function sanitizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+\-\s]/', '', $phone);
    }

    public static function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function sanitizeArray(array $input): array
    {
        return array_map(function ($value) {
            return is_string($value) ? self::sanitize($value) : $value;
        }, $input);
    }

    public static function removeHtml(string $input): string
    {
        return strip_tags($input);
    }
}
