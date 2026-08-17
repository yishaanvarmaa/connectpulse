<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ContactList;
use App\Models\ContactTag;
use App\Models\Lead;
use App\Services\CampaignService;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService,
        private ContactService $contactService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $campaigns = Campaign::forOrganization($organization)
            ->withCount('recipients')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('org.campaigns.index', compact('campaigns'));
    }

    public function create(Request $request): View
    {
        $organization = $request->user()->organization;

        $tags = ContactTag::forOrganization($organization)->orderBy('name')->get();
        $lists = ContactList::forOrganization($organization)->orderBy('name')->get();
        $contacts = $organization->contacts()->orderBy('name')->limit(500)->get();
        $leads = $organization->leads()->orderBy('name')->limit(500)->get();

        $defaults = [
            'delay_min' => config('connectpulse.campaign_delay_min_seconds', 10),
            'delay_max' => config('connectpulse.campaign_delay_max_seconds', 20),
        ];

        return view('org.campaigns.create', compact('tags', 'lists', 'contacts', 'leads', 'defaults'));
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message_body' => ['required', 'string', 'max:4096'],
            'audience_type' => ['required', 'in:'.implode(',', array_keys(Campaign::audienceTypes()))],
            'contact_list_id' => ['nullable', 'integer'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer'],
            'lead_ids' => ['nullable', 'array'],
            'lead_ids.*' => ['integer'],
            'csv_phones' => ['nullable', 'string'],
            'delay_min_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'delay_max_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'send_mode' => ['required', 'in:now,schedule'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'media' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'test_phone' => ['nullable', 'string', 'max:20'],
            'confirm_test' => ['nullable', 'boolean'],
            'launch' => ['nullable', 'boolean'],
        ]);

        [$minDelay, $maxDelay] = $this->campaignService->validateDelayRange(
            (int) $validated['delay_min_seconds'],
            (int) $validated['delay_max_seconds'],
        );

        $audienceConfig = match ($validated['audience_type']) {
            Campaign::AUDIENCE_CONTACT_LIST => ['contact_list_id' => $validated['contact_list_id'] ?? null],
            Campaign::AUDIENCE_TAGS => ['tag_ids' => $validated['tag_ids'] ?? []],
            Campaign::AUDIENCE_MANUAL => [
                'contact_ids' => $validated['contact_ids'] ?? [],
                'lead_ids' => $validated['lead_ids'] ?? [],
            ],
            Campaign::AUDIENCE_CSV => ['phones' => $this->parseCsvPhones($validated['csv_phones'] ?? '')],
            default => [],
        };

        $campaign = $this->campaignService->createDraft($organization, $request->user(), [
            'name' => $validated['name'],
            'message_body' => $validated['message_body'],
            'audience_type' => $validated['audience_type'],
            'audience_config' => $audienceConfig,
            'delay_min_seconds' => $minDelay,
            'delay_max_seconds' => $maxDelay,
            'scheduled_at' => $validated['send_mode'] === 'schedule' ? $validated['scheduled_at'] : null,
        ]);

        if ($request->hasFile('media')) {
            $this->campaignService->storeMedia($campaign, $request->file('media'));
        }

        $this->campaignService->buildRecipients($campaign);

        if (! empty($validated['test_phone'])) {
            $result = $this->campaignService->sendTest($campaign, $validated['test_phone']);
            if (! ($result['success'] ?? false)) {
                return redirect()
                    ->route('org.campaigns.show', $campaign)
                    ->with('error', $result['error'] ?? 'Test message failed.');
            }
        } elseif ($request->boolean('confirm_test')) {
            $campaign->update(['test_confirmed' => true]);
        }

        if ($request->boolean('launch')) {
            try {
                $this->campaignService->launch($campaign, $validated['send_mode'] === 'now');
            } catch (\RuntimeException $e) {
                return redirect()
                    ->route('org.campaigns.show', $campaign)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()
            ->route('org.campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Request $request, Campaign $campaign): View
    {
        $this->authorize('view', $campaign);

        $stats = $this->campaignService->getLiveStats($campaign);
        $recipients = $campaign->recipients()->orderByDesc('updated_at')->paginate(50);
        $mediaUrl = $this->campaignService->mediaUrl($campaign);

        return view('org.campaigns.show', [
            'campaign' => $stats['campaign'],
            'progressPercent' => $stats['progress_percent'],
            'pendingCount' => $stats['pending_count'],
            'currentRecipient' => $stats['current_recipient'],
            'estimatedCompletion' => $stats['estimated_completion'],
            'recipients' => $recipients,
            'mediaUrl' => $mediaUrl,
        ]);
    }

    public function status(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        $stats = $this->campaignService->getLiveStats($campaign);

        $recentRecipients = $campaign->recipients()
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name ?: $r->phone,
                'phone' => $r->phone,
                'status' => $r->status,
                'status_label' => $r->statusLabel(),
                'sent_at' => $r->sent_at?->format('g:i A'),
                'attempts' => $r->attempts,
                'failure_reason' => $r->failure_reason,
            ]);

        return response()->json([
            'status' => $stats['campaign']->status,
            'status_label' => $stats['campaign']->statusLabel(),
            'pause_reason' => $stats['campaign']->pause_reason,
            'sent_count' => $stats['campaign']->sent_count,
            'failed_count' => $stats['campaign']->failed_count,
            'total_recipients' => $stats['campaign']->total_recipients,
            'pending_count' => $stats['pending_count'],
            'progress_percent' => $stats['progress_percent'],
            'estimated_completion' => $stats['estimated_completion'],
            'current_recipient' => $stats['current_recipient'] ? [
                'name' => $stats['current_recipient']->name,
                'phone' => $stats['current_recipient']->phone,
                'status' => $stats['current_recipient']->status,
            ] : null,
            'recipients' => $recentRecipients,
            'credits_used' => $stats['campaign']->credits_used,
            'completed_at' => $stats['campaign']->completed_at?->toIso8601String(),
        ]);
    }

    public function pause(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        $this->campaignService->pause($campaign);

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        try {
            $this->campaignService->resume($campaign);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Campaign resumed.');
    }

    public function cancel(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        $this->campaignService->cancel($campaign);

        return back()->with('success', 'Campaign cancelled.');
    }

    public function retry(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        $this->campaignService->retryFailed($campaign);

        return back()->with('success', 'Retrying eligible failed recipients.');
    }

    public function test(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        $validated = $request->validate([
            'test_phone' => ['required', 'string', 'max:20'],
        ]);

        $result = $this->campaignService->sendTest($campaign, $validated['test_phone']);

        if (! ($result['success'] ?? false)) {
            return back()->with('error', $result['error'] ?? 'Test failed.');
        }

        return back()->with('success', 'Test message queued. Confirm to launch the campaign.');
    }

    public function confirmTest(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        $campaign->update(['test_confirmed' => true]);

        return back()->with('success', 'Test confirmed. You can now launch the campaign.');
    }

    public function launch(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        try {
            $this->campaignService->launch($campaign);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Campaign launched.');
    }

    private function parseCsvPhones(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $phones = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = str_getcsv($line);
            $phone = trim($parts[1] ?? $parts[0] ?? '');
            $name = trim($parts[0] ?? '') ?: null;

            if ($phone && $this->contactService->isValidPhone($phone)) {
                $phones[] = ['phone' => $phone, 'name' => $name !== $phone ? $name : null];
            }
        }

        return $phones;
    }
}
