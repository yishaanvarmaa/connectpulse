<?php

namespace App\Services\Messaging;

use App\Contracts\MessagingProviderInterface;
use App\Models\Organization;

class EmailProvider implements MessagingProviderInterface
{
    public function send(Organization $organization, string $mobile, string $message, ?string $mediaUrl = null): array
    {
        return [
            'success' => false,
            'message_id' => null,
            'error' => 'Email provider is not yet configured.',
        ];
    }

    public function getStatus(Organization $organization): array
    {
        return [
            'connected' => false,
            'phone' => null,
            'status' => 'not_configured',
        ];
    }

    public function disconnect(Organization $organization): bool
    {
        return false;
    }

    public function getQr(Organization $organization): ?string
    {
        return null;
    }
}
