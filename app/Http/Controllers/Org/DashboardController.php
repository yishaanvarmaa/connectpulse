<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;
        $stats = $this->messageService->getDashboardStats($organization);

        return view('org.dashboard', [
            'organization' => $organization,
            'stats' => $stats,
            'recentLogs' => $organization->messageLogs()->latest()->limit(10)->get(),
        ]);
    }
}
