<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Services\FollowUpService;
use App\Services\LeadActivityService;
use App\Services\LeadService;
use App\Services\LeadWhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(
        private LeadService $leadService,
        private FollowUpService $followUpService,
        private LeadActivityService $activityService,
        private LeadWhatsAppService $whatsappService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        return view('org.crm.leads.index', [
            'leads' => $this->leadService->search($organization, $request->only([
                'search', 'status', 'source', 'priority', 'product',
                'follow_up_from', 'follow_up_to', 'created_from', 'created_to', 'sort',
            ])),
            'statuses' => Lead::statuses(),
            'sources' => Lead::sources(),
            'priorities' => Lead::priorities(),
            'filters' => $request->only([
                'search', 'status', 'source', 'priority', 'product',
                'follow_up_from', 'follow_up_to', 'created_from', 'created_to', 'sort',
            ]),
        ]);
    }

    public function create(): View
    {
        return view('org.crm.leads.create', [
            'sources' => Lead::sources(),
            'priorities' => Lead::priorities(),
            'followUpTypes' => LeadFollowUp::types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'interested_product' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'source' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::sources()))],
            'next_follow_up_at' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', 'string', 'in:'.implode(',', array_keys(Lead::priorities()))],
            'follow_up_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(LeadFollowUp::types()))],
        ]);

        $lead = $this->leadService->create(
            $request->user()->organization,
            $request->user(),
            $validated
        );

        return redirect()
            ->route('org.crm.leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    public function show(Request $request, Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['followUps' => fn ($q) => $q->orderByDesc('scheduled_at'), 'creator']);

        return view('org.crm.leads.show', [
            'lead' => $lead,
            'timeline' => $this->activityService->getTimeline($lead),
            'communications' => $this->whatsappService->getCommunicationHistory($lead),
            'statuses' => Lead::statuses(),
            'followUpTypes' => LeadFollowUp::types(),
        ]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('org.crm.leads.edit', [
            'lead' => $lead,
            'sources' => Lead::sources(),
            'priorities' => Lead::priorities(),
            'statuses' => Lead::statuses(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'source' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::sources()))],
            'interested_product' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::statuses()))],
            'priority' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::priorities()))],
            'notes' => ['nullable', 'string', 'max:10000'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        $this->leadService->update($lead, $request->user(), $validated);

        return redirect()
            ->route('org.crm.leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::statuses()))],
            'lost_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->leadService->updateStatus(
            $lead,
            $request->user(),
            $validated['status'],
            $validated['lost_reason'] ?? null
        );

        return back()->with('success', 'Lead status updated.');
    }

    public function addNote(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $this->leadService->addNote($lead, $request->user(), $validated['note']);

        return back()->with('success', 'Note added.');
    }

    public function sendWhatsApp(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4096'],
        ]);

        try {
            $this->whatsappService->send($lead, $request->user(), $validated['message']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'WhatsApp message queued.');
    }

    public function storeFollowUp(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(LeadFollowUp::types()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->followUpService->create($lead, $request->user(), $validated);

        return back()->with('success', 'Follow-up scheduled.');
    }
}
