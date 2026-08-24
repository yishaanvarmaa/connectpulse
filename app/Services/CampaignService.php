<?php

namespace App\Services;

use App\Jobs\ProcessCampaignRecipientJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\MessageLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CampaignService
{
    public function __construct(
        private ContactService $contactService,
        private MessageService $messageService,
        private CreditService $creditService,
    ) {}

    public function validateDelayRange(int $min, int $max): array
    {
        $absoluteMin = config('connectpulse.campaign_delay_absolute_min', 5);
        $absoluteMax = config('connectpulse.campaign_delay_absolute_max', 300);

        $min = max($absoluteMin, $min);
        $max = min($absoluteMax, $max);

        if ($min > $max) {
            $max = $min;
        }

        return [$min, $max];
    }

    public function renderMessage(string $template, ?string $name): string
    {
        $displayName = $name ?: 'there';

        return str_replace('{{name}}', $displayName, $template);
    }

    public function createDraft(Organization $organization, User $user, array $data): Campaign
    {
        [$minDelay, $maxDelay] = $this->validateDelayRange(
            (int) ($data['delay_min_seconds'] ?? config('connectpulse.campaign_delay_min_seconds', 10)),
            (int) ($data['delay_max_seconds'] ?? config('connectpulse.campaign_delay_max_seconds', 20)),
        );

        return Campaign::create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
            'name' => $data['name'],
            'message_body' => $data['message_body'],
            'audience_type' => $data['audience_type'],
            'audience_config' => $data['audience_config'] ?? [],
            'status' => Campaign::STATUS_DRAFT,
            'delay_min_seconds' => $minDelay,
            'delay_max_seconds' => $maxDelay,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
    }

    public function storeMedia(Campaign $campaign, UploadedFile $file): Campaign
    {
        if ($campaign->media_path) {
            Storage::disk('public')->delete($campaign->media_path);
        }

        $path = $file->store("campaigns/{$campaign->organization_id}/{$campaign->id}", 'public');

        $campaign->update([
            'media_path' => $path,
            'media_type' => $file->getMimeType() ?: 'image/jpeg',
        ]);

        return $campaign->fresh();
    }

    public function buildRecipients(Campaign $campaign): int
    {
        $organization = $campaign->organization;
        $audience = $this->contactService->resolveAudience(
            $organization,
            $campaign->audience_type,
            $campaign->audience_config
        );

        DB::transaction(function () use ($campaign, $audience) {
            $campaign->recipients()->whereNotIn('status', [
                CampaignRecipient::STATUS_SENT,
                CampaignRecipient::STATUS_DELIVERED,
            ])->delete();

            foreach ($audience as $entry) {
                if (! is_array($entry) || empty($entry['phone'])) {
                    continue;
                }

                $rendered = $this->renderMessage($campaign->message_body, $entry['name'] ?? null);

                CampaignRecipient::updateOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'phone' => (string) $entry['phone'],
                    ],
                    [
                        'contact_id' => ! empty($entry['contact_id']) ? (int) $entry['contact_id'] : null,
                        'lead_id' => ! empty($entry['lead_id']) ? (int) $entry['lead_id'] : null,
                        'name' => $entry['name'] ?? null,
                        'rendered_message' => $rendered,
                        'status' => CampaignRecipient::STATUS_PENDING,
                    ]
                );
            }

            $total = $campaign->recipients()->count();
            $campaign->update(['total_recipients' => $total]);
        });

        return (int) ($campaign->fresh()->total_recipients ?? 0);
    }

    public function sendTest(Campaign $campaign, string $testPhone): array
    {
        $organization = $campaign->organization;
        $normalized = $this->contactService->normalizePhone($testPhone);

        if (! $this->contactService->isValidPhone($normalized)) {
            return ['success' => false, 'error' => 'Invalid test phone number. Use country code, e.g. 919876543210'];
        }

        $live = $this->messageService->provider()->getStatus($organization);
        if (! ($live['connected'] ?? false)) {
            return ['success' => false, 'error' => 'WhatsApp is not connected.'];
        }

        if (! $this->creditService->hasCredits($organization)) {
            return ['success' => false, 'error' => 'Insufficient credits for test message.'];
        }

        $message = $this->renderMessage($campaign->message_body, 'Test User');

        try {
            $log = $this->messageService->sendImmediate(
                $organization,
                $normalized,
                $message,
                $campaign->media_path,
                $campaign->media_type,
                $campaign->id,
            );
        } catch (\RuntimeException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        if ($log->status !== MessageLog::STATUS_SENT) {
            return [
                'success' => false,
                'error' => $log->error_message ?: 'Test message failed to send.',
            ];
        }

        // Save phone but do NOT auto-confirm — user must verify they received it.
        $campaign->update([
            'test_phone' => $normalized,
            'test_confirmed' => false,
        ]);

        return [
            'success' => true,
            'message_log_id' => $log->id,
            'has_media' => (bool) $campaign->media_path,
        ];
    }

    public function confirmTest(Campaign $campaign): Campaign
    {
        $campaign->update(['test_confirmed' => true]);

        return $campaign->fresh();
    }

    public function launch(Campaign $campaign, bool $immediate = true): Campaign
    {
        if (! $campaign->test_confirmed) {
            throw new \RuntimeException('Send a test message and confirm you received it before launching.');
        }

        if ($campaign->total_recipients <= 0) {
            $this->buildRecipients($campaign);
        }

        $campaign->refresh();

        if ($campaign->total_recipients <= 0) {
            throw new \RuntimeException('No valid recipients in audience.');
        }

        $organization = $campaign->organization;
        $live = $this->messageService->provider()->getStatus($organization);
        if (! ($live['connected'] ?? false)) {
            throw new \RuntimeException('WhatsApp is not connected.');
        }

        if ($immediate || ! $campaign->scheduled_at || $campaign->scheduled_at->isPast()) {
            $campaign->update([
                'status' => Campaign::STATUS_RUNNING,
                'started_at' => $campaign->started_at ?? now(),
                'pause_reason' => null,
            ]);

            ProcessCampaignRecipientJob::dispatch($campaign->id)
                ->onQueue('campaigns');
        } else {
            $campaign->update(['status' => Campaign::STATUS_SCHEDULED]);
        }

        return $campaign->fresh();
    }

    public function pause(Campaign $campaign, ?string $reason = null): Campaign
    {
        if ($campaign->status !== Campaign::STATUS_RUNNING) {
            return $campaign;
        }

        $campaign->update([
            'status' => Campaign::STATUS_PAUSED,
            'pause_reason' => $reason,
        ]);

        return $campaign->fresh();
    }

    public function resume(Campaign $campaign): Campaign
    {
        if ($campaign->status !== Campaign::STATUS_PAUSED) {
            return $campaign;
        }

        $organization = $campaign->organization;
        $live = $this->messageService->provider()->getStatus($organization);
        if (! ($live['connected'] ?? false)) {
            throw new \RuntimeException('WhatsApp is not connected.');
        }

        $campaign->update([
            'status' => Campaign::STATUS_RUNNING,
            'pause_reason' => null,
        ]);

        ProcessCampaignRecipientJob::dispatch($campaign->id)
            ->onQueue('campaigns');

        return $campaign->fresh();
    }

    public function cancel(Campaign $campaign): Campaign
    {
        if (in_array($campaign->status, [Campaign::STATUS_COMPLETED, Campaign::STATUS_CANCELLED], true)) {
            return $campaign;
        }

        $campaign->recipients()
            ->whereIn('status', [
                CampaignRecipient::STATUS_PENDING,
                CampaignRecipient::STATUS_QUEUED,
            ])
            ->update(['status' => CampaignRecipient::STATUS_CANCELLED]);

        $campaign->update([
            'status' => Campaign::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);

        $this->refreshStats($campaign);

        return $campaign->fresh();
    }

    public function delete(Campaign $campaign): void
    {
        if (! $campaign->canBeDeleted()) {
            throw new \RuntimeException('Only draft, completed, or cancelled campaigns can be deleted. Cancel a running campaign first.');
        }

        if ($campaign->media_path) {
            Storage::disk('public')->delete($campaign->media_path);
            $dir = dirname($campaign->media_path);
            if ($dir && $dir !== '.' && Storage::disk('public')->exists($dir)) {
                $files = Storage::disk('public')->files($dir);
                if (count($files) === 0) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
            }
        }

        $campaign->recipients()->delete();
        $campaign->delete();
    }

    public function retryFailed(Campaign $campaign): Campaign
    {
        $campaign->recipients()
            ->where('status', CampaignRecipient::STATUS_FAILED)
            ->where('attempts', '<', config('connectpulse.campaign_max_retries', 3))
            ->where(function ($q) {
                $q->whereNull('failure_reason')
                    ->orWhere(function ($q2) {
                        $q2->where('failure_reason', 'not like', '%invalid%')
                            ->where('failure_reason', 'not like', '%malformed%');
                    });
            })
            ->update([
                'status' => CampaignRecipient::STATUS_PENDING,
                'next_attempt_at' => null,
            ]);

        if ($campaign->status === Campaign::STATUS_COMPLETED) {
            $campaign->update([
                'status' => Campaign::STATUS_RUNNING,
                'completed_at' => null,
            ]);
        }

        if ($campaign->status === Campaign::STATUS_RUNNING) {
            ProcessCampaignRecipientJob::dispatch($campaign->id)
                ->onQueue('campaigns');
        }

        return $campaign->fresh();
    }

    public function processNextRecipient(Campaign $campaign): bool
    {
        $campaign->refresh();

        if (! in_array($campaign->status, [Campaign::STATUS_RUNNING], true)) {
            return false;
        }

        $recipient = $this->nextEligibleRecipient($campaign);

        if (! $recipient) {
            $this->completeIfDone($campaign);

            return false;
        }

        $organization = $campaign->organization;

        if (! $this->creditService->hasCredits($organization)) {
            $this->pause($campaign, 'Campaign paused — insufficient credits.');

            return false;
        }

        $recipient->update([
            'status' => CampaignRecipient::STATUS_SENDING,
            'attempts' => $recipient->attempts + 1,
            'queued_at' => now(),
        ]);

        try {
            $log = $this->messageService->sendCampaignMessage(
                $organization,
                $recipient->phone,
                $recipient->rendered_message ?? $this->renderMessage($campaign->message_body, $recipient->name),
                $recipient->lead_id,
                $campaign->id,
                $recipient->id,
                $campaign->media_path,
                $campaign->media_type,
            );
        } catch (\RuntimeException $e) {
            if (str_contains(strtolower($e->getMessage()), 'insufficient')) {
                $recipient->update(['status' => CampaignRecipient::STATUS_PENDING, 'attempts' => max(0, $recipient->attempts - 1)]);
                $this->pause($campaign, 'Campaign paused — insufficient credits.');

                return false;
            }

            $this->markRecipientFailed($recipient, $e->getMessage());
            $this->refreshStats($campaign);
            $this->scheduleNext($campaign);

            return true;
        }

        $recipient->refresh();
        $log->refresh();

        if ($log->status === MessageLog::STATUS_SENT) {
            $recipient->update([
                'status' => CampaignRecipient::STATUS_SENT,
                'message_log_id' => $log->id,
                'provider_message_id' => $log->message_id,
                'sent_at' => $log->sent_at ?? now(),
            ]);

            $campaign->increment('credits_used', $log->credits_used);

            if ($recipient->contact_id) {
                Contact::where('id', $recipient->contact_id)->update(['last_contacted_at' => now()]);
            }
        } else {
            $this->markRecipientFailed($recipient, $log->error_message ?? 'Delivery failed.');
        }

        $this->refreshStats($campaign);
        $this->scheduleNext($campaign);

        return true;
    }

    public function refreshStats(Campaign $campaign): Campaign
    {
        $sent = $campaign->recipients()->whereIn('status', [
            CampaignRecipient::STATUS_SENT,
            CampaignRecipient::STATUS_DELIVERED,
        ])->count();

        $failed = $campaign->recipients()->whereIn('status', [
            CampaignRecipient::STATUS_FAILED,
            CampaignRecipient::STATUS_SKIPPED,
        ])->count();

        $campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'total_recipients' => $campaign->recipients()->count(),
        ]);

        $this->completeIfDone($campaign);

        return $campaign->fresh();
    }

    public function getLiveStats(Campaign $campaign): array
    {
        $campaign = $this->refreshStats($campaign);

        $current = $campaign->recipients()
            ->where('status', CampaignRecipient::STATUS_SENDING)
            ->first();

        if (! $current) {
            $current = $campaign->recipients()
                ->whereIn('status', [CampaignRecipient::STATUS_PENDING, CampaignRecipient::STATUS_QUEUED])
                ->orderBy('id')
                ->first();
        }

        $pending = $campaign->recipients()->where('status', CampaignRecipient::STATUS_PENDING)->count();
        $avgDelay = ($campaign->delay_min_seconds + $campaign->delay_max_seconds) / 2;
        $estimatedSeconds = (int) ($pending * $avgDelay);

        return [
            'campaign' => $campaign,
            'progress_percent' => $campaign->progressPercent(),
            'pending_count' => $pending,
            'current_recipient' => $current,
            'estimated_completion' => $estimatedSeconds > 0
                ? now()->addSeconds($estimatedSeconds)->toIso8601String()
                : null,
        ];
    }

    public function mediaUrl(Campaign $campaign): ?string
    {
        if (! $campaign->media_path) {
            return null;
        }

        return Storage::disk('public')->url($campaign->media_path);
    }

    private function nextEligibleRecipient(Campaign $campaign): ?CampaignRecipient
    {
        $retryable = $campaign->recipients()
            ->where('status', CampaignRecipient::STATUS_FAILED)
            ->where('attempts', '<', config('connectpulse.campaign_max_retries', 3))
            ->where(function ($q) {
                $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now());
            })
            ->orderBy('id')
            ->first();

        if ($retryable && ! $retryable->isPermanentFailure()) {
            return $retryable;
        }

        return $campaign->recipients()
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->orderBy('id')
            ->first();
    }

    private function markRecipientFailed(CampaignRecipient $recipient, string $reason): void
    {
        $isPermanent = str_contains(strtolower($reason), 'invalid')
            || str_contains(strtolower($reason), 'malformed');

        $status = $isPermanent ? CampaignRecipient::STATUS_SKIPPED : CampaignRecipient::STATUS_FAILED;

        $recipient->update([
            'status' => $status,
            'failed_at' => now(),
            'failure_reason' => $reason,
            'next_attempt_at' => $status === CampaignRecipient::STATUS_FAILED
                ? now()->addMinutes(5)
                : null,
        ]);
    }

    private function completeIfDone(Campaign $campaign): void
    {
        if ($campaign->status !== Campaign::STATUS_RUNNING) {
            return;
        }

        $remaining = $campaign->recipients()
            ->whereIn('status', [
                CampaignRecipient::STATUS_PENDING,
                CampaignRecipient::STATUS_QUEUED,
                CampaignRecipient::STATUS_SENDING,
            ])
            ->count();

        if ($remaining === 0) {
            $campaign->update([
                'status' => Campaign::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }

    private function scheduleNext(Campaign $campaign): void
    {
        $campaign->refresh();

        if ($campaign->status !== Campaign::STATUS_RUNNING) {
            return;
        }

        $remaining = $campaign->recipients()
            ->whereIn('status', [
                CampaignRecipient::STATUS_PENDING,
                CampaignRecipient::STATUS_FAILED,
            ])
            ->exists();

        if (! $remaining) {
            $this->completeIfDone($campaign);

            return;
        }

        $delay = random_int($campaign->delay_min_seconds, $campaign->delay_max_seconds);

        ProcessCampaignRecipientJob::dispatch($campaign->id)
            ->onQueue('campaigns')
            ->delay(now()->addSeconds($delay));
    }
}
