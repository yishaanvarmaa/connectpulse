<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\ApiKeyService;
use Illuminate\Http\RedirectResponse;

class OrganizationApiKeyController extends Controller
{
    public function __construct(
        private ApiKeyService $apiKeyService
    ) {}

    public function regenerateKey(Organization $organization): RedirectResponse
    {
        $this->apiKeyService->regenerateKey($organization);

        return back()->with('success', 'API key regenerated successfully.');
    }

    public function regenerateSecret(Organization $organization): RedirectResponse
    {
        $this->apiKeyService->regenerateSecret($organization);

        return back()->with('success', 'API secret regenerated successfully.');
    }

    public function regenerateBoth(Organization $organization): RedirectResponse
    {
        $this->apiKeyService->regenerate($organization);

        return back()->with('success', 'API key and secret regenerated successfully.');
    }
}
