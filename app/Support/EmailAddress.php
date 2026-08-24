<?php

namespace App\Support;

final class EmailAddress
{
    /**
     * @var list<string>
     */
    private const DOTLESS_DOMAINS = ['gmail.com', 'googlemail.com'];

    public static function normalize(string $email): string
    {
        $email = strtolower(trim($email));

        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        if (str_contains($local, '+')) {
            $local = strstr($local, '+', true) ?: $local;
        }

        if (in_array($domain, self::DOTLESS_DOMAINS, true)) {
            $local = str_replace('.', '', $local);
            $domain = 'gmail.com';
        }

        return $local.'@'.$domain;
    }

    public static function domain(string $email): string
    {
        $email = strtolower(trim($email));

        if (! str_contains($email, '@')) {
            return '';
        }

        return substr($email, (int) strrpos($email, '@') + 1);
    }
}
