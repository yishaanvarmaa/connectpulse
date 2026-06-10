<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Organization;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function generateForOrganization(Organization $organization): ApiKey
    {
        $prefix = config('connectpulse.api_key_prefix', 'cp_live_');
        $apiKey = $prefix.Str::random(24);
        $apiSecret = Str::random(48);

        return ApiKey::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'is_active' => true,
            ]
        );
    }

    public function regenerate(Organization $organization): ApiKey
    {
        return $this->generateForOrganization($organization);
    }

    public function regenerateKey(Organization $organization): ApiKey
    {
        $prefix = config('connectpulse.api_key_prefix', 'cp_live_');
        $apiKey = $organization->apiKey ?? $this->generateForOrganization($organization);

        $apiKey->update([
            'api_key' => $prefix.Str::random(24),
            'is_active' => true,
        ]);

        return $apiKey->fresh();
    }

    public function regenerateSecret(Organization $organization): ApiKey
    {
        $apiKey = $organization->apiKey ?? $this->generateForOrganization($organization);

        $apiKey->update([
            'api_secret' => Str::random(48),
            'is_active' => true,
        ]);

        return $apiKey->fresh();
    }

    public function findByCredentials(string $apiKey, string $apiSecret): ?ApiKey
    {
        $key = ApiKey::where('api_key', $apiKey)
            ->where('is_active', true)
            ->with('organization')
            ->first();

        if (! $key) {
            return null;
        }

        if ($key->api_secret !== $apiSecret) {
            return null;
        }

        return $key;
    }
}
