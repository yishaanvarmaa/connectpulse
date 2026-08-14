<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineController extends Controller
{
    public function __construct(
        private LeadService $leadService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $leads = $this->leadService->getPipelineLeads($organization);

        $columns = [];
        foreach (Lead::statuses() as $key => $label) {
            $columns[$key] = [
                'label' => $label,
                'leads' => $leads->where('status', $key)->values(),
            ];
        }

        return view('org.crm.pipeline.index', [
            'columns' => $columns,
            'statuses' => Lead::statuses(),
        ]);
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::statuses()))],
        ]);

        $this->leadService->updateStatus($lead, $request->user(), $validated['status']);

        return back()->with('success', 'Lead moved to '.$lead->fresh()->statusLabel().'.');
    }
}
