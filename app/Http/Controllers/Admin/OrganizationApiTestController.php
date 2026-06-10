<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class OrganizationApiTestController extends Controller
{
    public function show(Organization $organization): View
    {
        $organization->load(['apiKey', 'whatsappConnection', 'creditWallet']);

        return view('admin.organizations.api-test', [
            'organization' => $organization,
            'lastResponse' => session('api_test_response'),
            'lastStatus' => session('api_test_status'),
        ]);
    }

    public function send(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:15'],
            'message' => ['required', 'string', 'max:4096'],
        ]);

        $organization->load('apiKey');

        if (! $organization->apiKey) {
            return back()->with('error', 'No API credentials found for this organization.');
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'X-API-Key' => $organization->apiKey->api_key,
                'X-API-Secret' => $organization->apiKey->api_secret,
                'Accept' => 'application/json',
            ])
            ->post(url('/api/v1/messages/send'), [
                'mobile' => $validated['mobile'],
                'message' => $validated['message'],
            ]);

        $body = $response->json() ?? ['raw' => $response->body()];
        $success = $response->successful() && ($body['success'] ?? false);

        return back()
            ->withInput()
            ->with('api_test_status', $success ? 'success' : 'failure')
            ->with('api_test_response', [
                'http_status' => $response->status(),
                'body' => $body,
            ]);
    }
}
