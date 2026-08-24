<?php

namespace App\Support;

final class PhoneNumber
{
    public static function digits(string $mobile): string
    {
        return preg_replace('/\D+/', '', $mobile) ?? '';
    }

    /**
     * Indian mobile as 10 digits (strips leading 91 / 0).
     */
    public static function national(string $mobile): string
    {
        $digits = self::digits($mobile);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }

    public static function isValidIndianMobile(string $mobile): bool
    {
        return (bool) preg_match('/^[6-9]\d{9}$/', self::national($mobile));
    }
}
