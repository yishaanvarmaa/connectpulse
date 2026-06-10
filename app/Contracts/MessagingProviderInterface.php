<?php

namespace App\Contracts;

use App\Models\Organization;

interface MessagingProviderInterface
{
    public function send(Organization $organization, string $mobile, string $message): array;

    public function getStatus(Organization $organization): array;

    public function disconnect(Organization $organization): bool;

    public function getQr(Organization $organization): ?string;
}
