<?php

namespace App\Core\Helpers;

class ValidationHelper
{
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function isValidPhone(string $phone): bool
    {
        // Simple validation - at least 9 digits
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        return strlen($cleaned) >= 9;
    }

    public static function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(.)\1+$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            $m = $t + 1;

            for ($i = 0; $i < $t; $i++) {
                $d += $cpf[$i] * --$m;
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$t] != $d) {
                return false;
            }
        }

        return true;
    }

    public static function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 12
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }

    public static function passwordStrength(string $password): int
    {
        $strength = 0;

        $strength += strlen($password) >= 12 ? 25 : 0;
        $strength += strlen($password) >= 16 ? 25 : 0;
        $strength += preg_match('/[A-Z]/', $password) ? 25 : 0;
        $strength += preg_match('/[0-9]/', $password) ? 15 : 0;
        $strength += preg_match('/[^A-Za-z0-9]/', $password) ? 10 : 0;

        return min($strength, 100);
    }
}
