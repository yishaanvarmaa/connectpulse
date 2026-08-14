<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $period = $request->input('period', '30d');

        return view('org.crm.reports.index', [
            'report' => $this->reportService->getReport(
                $organization,
                $period,
                $request->input('from'),
                $request->input('to'),
            ),
            'period' => $period,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);
    }
}
