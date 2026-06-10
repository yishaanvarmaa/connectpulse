<?php

namespace App\Http\Middleware;

use App\Services\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(
        private ApiKeyService $apiKeyService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->header('X-API-KEY');
        $apiSecret = $request->header('X-API-Secret') ?? $request->header('X-API-SECRET');

        if (! $apiKey || ! $apiSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API credentials.',
            ], 401);
        }

        $key = $this->apiKeyService->findByCredentials($apiKey, $apiSecret);

        if (! $key || ! $key->organization) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API credentials.',
            ], 401);
        }

        if (! $key->organization->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Organization is not active.',
            ], 403);
        }

        $organization = $key->organization;
        $organization->loadMissing('whatsappConnection');

        $request->attributes->set('organization', $organization);
        $request->attributes->set('api_key', $key);

        return $next($request);
    }
}
