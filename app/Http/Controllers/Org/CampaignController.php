<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ContactList;
use App\Models\ContactTag;
use App\Services\CampaignService;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function create(Request $request): RedirectResponse|Response
    {
        try {
            $organization = $request->user()->organization;

            if (! $organization) {
                return redirect()
                    ->route('org.campaigns.index')
                    ->with('error', 'No organization found for your account.');
            }

            $tags = ContactTag::forOrganization($organization)->withCount('contacts')->orderBy('name')->get();
            $lists = ContactList::forOrganization($organization)->withCount('contacts')->orderBy('name')->get();
            $contacts = $organization->contacts()
                ->select(['id', 'name', 'phone'])
                ->orderBy('name')
                ->limit(500)
                ->get();
            $leads = $organization->leads()
                ->select(['id', 'name', 'phone'])
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->orderBy('name')
                ->limit(500)
                ->get();

            $defaults = [
                'delay_min' => (int) config('connectpulse.campaign_delay_min_seconds', 10),
                'delay_max' => (int) config('connectpulse.campaign_delay_max_seconds', 20),
            ];

            $audienceMeta = [
                'total_contacts' => $organization->contacts()->count(),
                'total_leads' => $organization->leads()->whereNotNull('phone')->where('phone', '!=', '')->count(),
                'lists' => $lists->mapWithKeys(fn ($l) => [(string) $l->id => (int) $l->contacts_count])->all(),
                'tags' => $tags->mapWithKeys(fn ($t) => [(string) $t->id => (int) $t->contacts_count])->all(),
            ];

            $businessName = $organization->company_name ?: 'Your Business';
            $audienceMetaJson = json_encode(
                $audienceMeta,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            ) ?: '{}';

            $html = view('org.campaigns.create', [
                'tags' => $tags,
                'lists' => $lists,
                'contacts' => $contacts,
                'leads' => $leads,
                'defaults' => $defaults,
                'audienceMeta' => $audienceMeta,
                'audienceMetaJson' => $audienceMetaJson,
                'organization' => $organization,
                'businessName' => $businessName,
            ])->render();

            return response($html);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('org.campaigns.index')
                ->with('error', 'Could not open campaign composer: '.$e->getMessage());
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $debugLog = storage_path('logs/campaign-debug.log');
        $debug = function (string $message) use ($debugLog): void {
            @file_put_contents(
                $debugLog,
                '['.now()->toDateTimeString().'] '.$message.PHP_EOL,
                FILE_APPEND
            );
        };

        $debug('STORE hit by user #'.($request->user()?->id ?? 'guest').' ip='.$request->ip());

        try {
            $organization = $request->user()->organization;

            if (! $organization) {
                $debug('No organization on user');

                return redirect()
                    ->route('org.campaigns.index')
                    ->with('error', 'No organization found for your account.');
            }

            // Defaults so a missing delay field never hard-fails the request.
            $request->merge([
                'delay_min_seconds' => $request->input(
                    'delay_min_seconds',
                    config('connectpulse.campaign_delay_min_seconds', 10)
                ),
                'delay_max_seconds' => $request->input(
                    'delay_max_seconds',
                    config('connectpulse.campaign_delay_max_seconds', 20)
                ),
                'send_mode' => $request->input('send_mode', 'now'),
            ]);

            $debug('Payload keys: '.implode(',', array_keys($request->except(['media', '_token']))));
            $debug('Has media: '.($request->hasFile('media') ? 'yes' : 'no'));

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
                'scheduled_at' => ['nullable', 'required_if:send_mode,schedule', 'date', 'after:now'],
                'media' => ['nullable', 'file', 'max:5120'],
            ]);

            $debug('Validated audience_type='.$validated['audience_type']);

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

            $scheduledAt = $validated['send_mode'] === 'schedule'
                ? ($validated['scheduled_at'] ?? null)
                : null;

            $campaign = $this->campaignService->createDraft($organization, $request->user(), [
                'name' => $validated['name'],
                'message_body' => $validated['message_body'],
                'audience_type' => $validated['audience_type'],
                'audience_config' => $audienceConfig,
                'delay_min_seconds' => $minDelay,
                'delay_max_seconds' => $maxDelay,
                'scheduled_at' => $scheduledAt ?: null,
            ]);

            $debug('Draft created id='.$campaign->id);

            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $mime = (string) ($file->getMimeType() ?: '');
                $debug('Media mime='.$mime.' size='.$file->getSize());
                if ($mime !== '' && ! str_starts_with($mime, 'image/')) {
                    return redirect()
                        ->route('org.campaigns.create')
                        ->withInput()
                        ->with('error', 'Please upload an image file (JPG, PNG, WebP, or GIF).');
                }
                $this->campaignService->storeMedia($campaign, $file);
                $debug('Media stored path='.$campaign->fresh()->media_path);
            }

            $recipientCount = $this->campaignService->buildRecipients($campaign);
            $debug('Recipients built count='.$recipientCount);

            if ($recipientCount <= 0) {
                return redirect()
                    ->route('org.campaigns.show', $campaign)
                    ->with('error', 'Campaign saved, but no valid recipients were found. Check your audience.');
            }

            $debug('STORE success → redirect show #'.$campaign->id);

            return redirect()
                ->route('org.campaigns.show', $campaign)
                ->with('success', 'Campaign saved. Send yourself a test message, then launch.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $debug('VALIDATION: '.json_encode($e->errors()));
            throw $e;
        } catch (\Throwable $e) {
            $debug('ERROR '.$e::class.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
            report($e);

            return redirect()
                ->route('org.campaigns.create')
                ->withInput()
                ->with('error', 'Could not save campaign: '.$e->getMessage());
        }
    }

    public function show(Request $request, Campaign $campaign): View|RedirectResponse
    {
        $debugLog = storage_path('logs/campaign-debug.log');
        @file_put_contents(
            $debugLog,
            '['.now()->toDateTimeString().'] SHOW hit campaign #'.$campaign->id.PHP_EOL,
            FILE_APPEND
        );

        $this->authorize('view', $campaign);

        try {
            $stats = $this->campaignService->getLiveStats($campaign);
            $recipients = $campaign->recipients()->orderByDesc('updated_at')->paginate(50);
            $mediaUrl = $this->campaignService->mediaUrl($campaign);

            @file_put_contents(
                $debugLog,
                '['.now()->toDateTimeString().'] SHOW ok #'.$campaign->id.PHP_EOL,
                FILE_APPEND
            );

            return view('org.campaigns.show', [
                'campaign' => $stats['campaign'],
                'progressPercent' => $stats['progress_percent'],
                'pendingCount' => $stats['pending_count'],
                'currentRecipient' => $stats['current_recipient'],
                'estimatedCompletion' => $stats['estimated_completion'],
                'recipients' => $recipients,
                'mediaUrl' => $mediaUrl,
            ]);
        } catch (\Throwable $e) {
            @file_put_contents(
                $debugLog,
                '['.now()->toDateTimeString().'] SHOW ERROR '.$e::class.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine().PHP_EOL,
                FILE_APPEND
            );
            report($e);

            return redirect()
                ->route('org.campaigns.index')
                ->with('error', 'Could not open campaign: '.$e->getMessage());
        }
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

    public function destroy(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);

        try {
            $this->campaignService->delete($campaign);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('org.campaigns.index')
            ->with('success', 'Campaign deleted.');
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

        $mediaNote = ($result['has_media'] ?? false)
            ? ' (with your photo)'
            : '';

        return back()->with('success', "Test message sent{$mediaNote}. Check WhatsApp, then confirm below to unlock launch.");
    }

    public function confirmTest(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        $this->campaignService->confirmTest($campaign);

        return back()->with('success', 'Test confirmed. You can now launch the campaign.');
    }

    public function launch(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        if ($request->boolean('skip_test')) {
            $this->campaignService->confirmTest($campaign);
            $campaign->refresh();
        }

        // "Launch now" / send_now=1 always starts immediately, even if a
        // future schedule was saved on the draft.
        $sendNow = $request->boolean('send_now', true);
        $hasFutureSchedule = $campaign->scheduled_at && $campaign->scheduled_at->isFuture();
        $immediate = $sendNow || ! $hasFutureSchedule;

        try {
            $this->campaignService->launch($campaign, $immediate);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $campaign->refresh();

        $message = $campaign->status === 'running'
            ? 'Campaign launched — first message is sending now.'
            : ($campaign->scheduled_at
                ? 'Campaign scheduled for '.$campaign->scheduled_at->format('M j, g:i A').'.'
                : 'Campaign launched.');

        return back()->with('success', $message);
    }

    public function kick(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('manage', $campaign);

        try {
            $this->campaignService->kick($campaign);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Sending resumed.');
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
