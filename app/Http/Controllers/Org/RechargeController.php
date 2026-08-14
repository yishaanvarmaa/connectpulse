<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechargeController extends Controller
{
    public function __construct(
        private MessageService $messageService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $organization->load('creditWallet');
        $stats = $this->messageService->getDashboardStats($organization);

        $usedThisMonth = $organization->creditTransactions()
            ->where('type', 'debit')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        return view('org.recharge.index', [
            'organization' => $organization,
            'transactions' => $organization->creditTransactions()->latest()->paginate(20),
            'messagesToday' => $stats['messages_today'] ?? 0,
            'messagesMonth' => $stats['messages_this_month'] ?? 0,
            'usedThisMonth' => (int) $usedThisMonth,
        ]);
    }
}
