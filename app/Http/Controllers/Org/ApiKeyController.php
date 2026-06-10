<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Services\ApiKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function __construct(
        private ApiKeyService $apiKeyService
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $organization->load('apiKey');

        return view('org.api-keys.index', [
            'organization' => $organization,
            'apiKey' => $organization->apiKey,
        ]);
    }

    public function regenerate(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        $this->apiKeyService->regenerate($organization);

        return back()->with('success', 'API keys regenerated successfully. Update your integrations.');
    }
}
