<?php

namespace App\Http\Controllers\Org\Crm;

use App\Http\Controllers\Controller;
use App\Models\LeadFollowUp;
use App\Services\FollowUpService;
use App\Services\LeadWhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function __construct(
        private FollowUpService $followUpService,
        private LeadWhatsAppService $whatsappService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $groups = $this->followUpService->getDashboardGroups($organization);

        return view('org.crm.follow-ups.index', [
            'groups' => $groups,
        ]);
    }

    public function complete(Request $request, LeadFollowUp $followUp): RedirectResponse
    {
        $this->authorize('manageFollowUp', $followUp);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->followUpService->complete($followUp, $request->user(), $validated['notes'] ?? null);

        return back()->with('success', 'Follow-up marked as completed.');
    }

    public function reschedule(Request $request, LeadFollowUp $followUp): RedirectResponse
    {
        $this->authorize('manageFollowUp', $followUp);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
        ]);

        $this->followUpService->reschedule($followUp, $request->user(), $validated['scheduled_at']);

        return back()->with('success', 'Follow-up rescheduled.');
    }

    public function cancel(Request $request, LeadFollowUp $followUp): RedirectResponse
    {
        $this->authorize('manageFollowUp', $followUp);

        $this->followUpService->cancel($followUp, $request->user());

        return back()->with('success', 'Follow-up cancelled.');
    }

    public function sendWhatsApp(Request $request, LeadFollowUp $followUp): RedirectResponse
    {
        $this->authorize('manageFollowUp', $followUp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4096'],
        ]);

        try {
            $this->whatsappService->send($followUp->lead, $request->user(), $validated['message']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'WhatsApp message queued.');
    }
}
