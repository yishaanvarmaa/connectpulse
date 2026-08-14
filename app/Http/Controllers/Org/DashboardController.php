<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Services\CrmDashboardService;
use App\Services\FollowUpService;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private CrmDashboardService $crmDashboardService,
        private FollowUpService $followUpService,
    ) {}

    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;
        $stats = $this->messageService->getDashboardStats($organization);

        $data = [
            'organization' => $organization,
            'stats' => $stats,
            'recentLogs' => $organization->messageLogs()->latest()->limit(8)->get(),
        ];

        if ($request->user()->isOrganizationAdmin()) {
            $data['crmStats'] = $this->crmDashboardService->getStats($organization);
            $data['sourceAnalytics'] = $this->crmDashboardService->getSourceAnalytics($organization);
            $groups = $this->followUpService->getDashboardGroups($organization);
            $data['followUpsToday'] = $groups['today'];
            $data['followUpsOverdue'] = $groups['overdue'];
        }

        return view('org.dashboard', $data);
    }
}
