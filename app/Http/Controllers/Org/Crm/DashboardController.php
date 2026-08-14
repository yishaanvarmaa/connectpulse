<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Services\CrmDashboardService;
use App\Services\FollowUpService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private CrmDashboardService $dashboardService,
    ) {}

    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;

        return view('org.crm.dashboard', [
            'organization' => $organization,
            'stats' => $this->dashboardService->getStats($organization),
            'sourceAnalytics' => $this->dashboardService->getSourceAnalytics($organization),
            'followUpGroups' => app(FollowUpService::class)->getDashboardGroups($organization),
        ]);
    }
}
