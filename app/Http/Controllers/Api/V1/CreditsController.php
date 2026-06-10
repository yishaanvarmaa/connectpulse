<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditsController extends Controller
{
    public function __construct(
        private CreditService $creditService
    ) {}

    public function balance(Request $request): JsonResponse
    {
        /** @var Organization $organization */
        $organization = $request->attributes->get('organization');

        $balance = $this->creditService->getBalance($organization);

        return response()->json([
            'balance' => $balance,
            'credits_remaining' => $balance,
        ]);
    }
}
