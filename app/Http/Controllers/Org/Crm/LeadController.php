<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
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
                'follow_up_from', 'follow_up_to', 'created_from', 'created_to', 'sort', 'view',
            ])),
            'statuses' => Lead::statuses(),
            'sources' => Lead::sources(),
            'priorities' => Lead::priorities(),
            'filters' => $request->only([
                'search', 'status', 'source', 'priority', 'product',
                'follow_up_from', 'follow_up_to', 'created_from', 'created_to', 'sort', 'view',
            ]),
            'quickFilters' => Lead::quickFilters(),
            'view' => $request->input('view', 'all'),
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
        $request->merge([
            'phone' => preg_replace('/\D+/', '', (string) $request->input('phone', '')),
            'estimated_value' => $request->filled('estimated_value') ? $request->input('estimated_value') : null,
            'email' => $request->filled('email') ? $request->input('email') : null,
            'next_follow_up_at' => $request->filled('next_follow_up_at') ? $request->input('next_follow_up_at') : null,
        ]);

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
            ->route('org.crm.leads.index')
            ->with('success', "Lead \"{$lead->name}\" saved successfully.");
    }

    public function show(Request $request, Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['followUps' => fn ($q) => $q->orderByDesc('scheduled_at'), 'creator']);

        $pendingFollowUp = $lead->followUps()
            ->pending()
            ->overdue()
            ->orderBy('scheduled_at')
            ->first()
            ?? $lead->followUps()->pending()->dueToday()->orderBy('scheduled_at')->first()
            ?? $lead->followUps()->pending()->orderBy('scheduled_at')->first();

        return view('org.crm.leads.show', [
            'lead' => $lead,
            'timeline' => $this->activityService->getTimeline($lead),
            'communications' => $this->whatsappService->getCommunicationHistory($lead),
            'statuses' => Lead::statuses(),
            'followUpTypes' => LeadFollowUp::types(),
            'outcomes' => LeadActivity::outcomes(),
            'pendingFollowUp' => $pendingFollowUp,
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

    public function logInteraction(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $request->merge([
            'next_scheduled_at' => $request->filled('next_scheduled_at') ? $request->input('next_scheduled_at') : null,
        ]);

        $validated = $request->validate([
            'outcome' => ['required_without:result', 'string', 'in:'.implode(',', array_keys(LeadActivity::outcomes()))],
            'result' => ['required_without:outcome', 'string', 'in:interested,needs_time,demo,negotiation,won,lost,no_response'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'follow_up_id' => ['nullable', 'integer', 'exists:lead_follow_ups,id'],
            'next_scheduled_at' => ['nullable', 'date'],
            'next_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(LeadFollowUp::types()))],
        ]);

        $followUp = null;
        if (! empty($validated['follow_up_id'])) {
            $followUp = $this->followUpService->findForOrganization(
                $request->user()->organization,
                (int) $validated['follow_up_id']
            );

            if ($followUp->lead_id !== $lead->id) {
                abort(403);
            }
        }

        $this->followUpService->logInteraction($lead, $request->user(), $validated, $followUp);

        return back()->with('success', 'Follow-up logged and saved to timeline.');
    }

    public function updateTemperature(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'temperature' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::temperatures()))],
        ]);

        $lead->update(['temperature' => $validated['temperature']]);

        return back()->with('success', 'Lead marked as '.$lead->fresh()->temperatureLabel().'.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['integer'],
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Lead::statuses()))],
        ]);

        $organization = $request->user()->organization;
        $updated = 0;

        foreach ($validated['lead_ids'] as $leadId) {
            $lead = Lead::forOrganization($organization)->find($leadId);
            if (! $lead) {
                continue;
            }
            $this->authorize('update', $lead);
            $this->leadService->updateStatus($lead, $request->user(), $validated['status']);
            $updated++;
        }

        return back()->with('success', "{$updated} lead".($updated === 1 ? '' : 's').' updated.');
    }
}
