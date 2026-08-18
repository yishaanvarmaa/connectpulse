<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Services\CommandCenterService;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private CommandCenterService $commandCenter,
    ) {}

    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;
        $stats = $this->messageService->getDashboardStats($organization);

        $data = [
            'organization' => $organization,
            'stats' => $stats,
        ];

        if ($request->user()->isOrganizationAdmin()) {
            try {
                $data = array_merge($data, $this->commandCenter->getHomeData($organization));
            } catch (\Throwable) {
                $data['recentLogs'] = $organization->messageLogs()->latest()->limit(5)->get();
            }
        } else {
            $data['recentLogs'] = $organization->messageLogs()->latest()->limit(8)->get();
        }

        return view('org.dashboard', $data);
    }
}
