<?php

namespace App\Rules;

use App\Support\EmailAddress;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotDisposableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $domain = EmailAddress::domain($value);

        if ($domain === '') {
            return;
        }

        $blocked = $this->blockedDomains();

        foreach ($this->domainCandidates($domain) as $candidate) {
            if (isset($blocked[$candidate])) {
                $fail('Use a real work or personal email. Temporary and disposable addresses are not allowed.');

                return;
            }
        }
    }

    /**
     * @return array<string, true>
     */
    private function blockedDomains(): array
    {
        $domains = config('disposable_email_domains', []);
        $extra = config('connectpulse.extra_disposable_email_domains', []);

        $map = [];

        foreach (array_merge($domains, $extra) as $domain) {
            $domain = strtolower(trim((string) $domain));

            if ($domain !== '') {
                $map[$domain] = true;
            }
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    private function domainCandidates(string $domain): array
    {
        $parts = explode('.', $domain);
        $candidates = [];

        for ($i = 0; $i < count($parts) - 1; $i++) {
            $candidates[] = implode('.', array_slice($parts, $i));
        }

        return $candidates;
    }
}
