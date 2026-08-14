<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Services\CrmDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private CrmDashboardService $dashboardService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        return view('org.crm.reports.index', [
            'stats' => $this->dashboardService->getStats($organization),
            'sourceAnalytics' => $this->dashboardService->getSourceAnalytics($organization),
        ]);
    }
}
