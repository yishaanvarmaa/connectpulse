<?php

namespace App\Services;

use App\Contracts\MessagingProviderInterface;
use App\Jobs\SendWhatsAppMessage;
use App\Models\MessageLog;
use App\Models\Organization;
use App\Services\Messaging\WhatsAppWebProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageService
{
    public function __construct(
        private CreditService $creditService,
        private WhatsAppWebProvider $provider,
    ) {}

    public function provider(): MessagingProviderInterface
    {
        return $this->provider;
    }

    public function queueMessage(Organization $organization, string $mobile, string $message, ?int $leadId = null): MessageLog
    {
        return DB::transaction(function () use ($organization, $mobile, $message, $leadId) {
            if (! $this->creditService->deductCredit($organization, "Queued message to {$mobile}")) {
                throw new \RuntimeException('Insufficient credits.');
            }

            $log = MessageLog::create([
                'organization_id' => $organization->id,
                'lead_id' => $leadId,
                'mobile' => $this->normalizeMobile($mobile),
                'message' => $message,
                'status' => MessageLog::STATUS_QUEUED,
                'credits_used' => 1,
            ]);

            SendWhatsAppMessage::dispatch($log->id)
                ->onQueue('messages');

            return $log;
        });
    }

    /**
     * Send a campaign message synchronously through the existing provider pipeline.
     * Credits are deducted once; no duplicate charge on retries because recipient status gates re-entry.
     */
    public function sendCampaignMessage(
        Organization $organization,
        string $mobile,
        string $message,
        ?int $leadId,
        int $campaignId,
        int $campaignRecipientId,
        ?string $mediaPath = null,
        ?string $mediaType = null,
    ): MessageLog {
        return DB::transaction(function () use (
            $organization,
            $mobile,
            $message,
            $leadId,
            $campaignId,
            $campaignRecipientId,
            $mediaPath,
            $mediaType,
        ) {
            if (! $this->creditService->deductCredit($organization, "Campaign message to {$mobile}")) {
                throw new \RuntimeException('Insufficient credits.');
            }

            $log = MessageLog::create([
                'organization_id' => $organization->id,
                'lead_id' => $leadId,
                'mobile' => $this->normalizeMobile($mobile),
                'message' => $message,
                'media_path' => $mediaPath,
                'media_type' => $mediaType,
                'status' => MessageLog::STATUS_QUEUED,
                'credits_used' => 1,
                'campaign_id' => $campaignId,
                'campaign_recipient_id' => $campaignRecipientId,
            ]);

            $this->processMessage($log);

            return $log->fresh();
        });
    }

    /**
     * @return array{batch_id: string, queued: int}
     */
    public function queueBulk(Organization $organization, array $recipients): array
    {
        return DB::transaction(function () use ($organization, $recipients) {
            $batchId = (string) Str::uuid();
            $queued = 0;

            foreach ($recipients as $recipient) {
                $mobile = is_array($recipient) ? ($recipient['mobile'] ?? '') : $recipient;
                $message = is_array($recipient) ? ($recipient['message'] ?? '') : '';

                if (empty($mobile) || empty($message)) {
                    continue;
                }

                if (! $this->creditService->deductCredit($organization, "Queued bulk message to {$mobile}")) {
                    break;
                }

                $log = MessageLog::create([
                    'organization_id' => $organization->id,
                    'mobile' => $this->normalizeMobile($mobile),
                    'message' => $message,
                    'status' => MessageLog::STATUS_QUEUED,
                    'credits_used' => 1,
                    'batch_id' => $batchId,
                ]);

                SendWhatsAppMessage::dispatch($log->id)
                    ->onQueue('messages');

                $queued++;
            }

            return [
                'batch_id' => $batchId,
                'queued' => $queued,
            ];
        });
    }

    public function processMessage(MessageLog $log): void
    {
        $organization = $log->organization;

        if (! $organization->isActive()) {
            $log->update([
                'status' => MessageLog::STATUS_FAILED,
                'error_message' => 'Organization is not active.',
            ]);

            return;
        }

        // Live bridge status is source of truth (DB can be stale after crypto resets)
        $live = $this->provider->getStatus($organization);
        if (! ($live['connected'] ?? false)) {
            $log->update([
                'status' => MessageLog::STATUS_FAILED,
                'error_message' => 'WhatsApp not connected.',
            ]);

            return;
        }

        $mediaUrl = null;
        if ($log->media_path) {
            $mediaUrl = url(Storage::disk('public')->url($log->media_path));
        }

        $result = $this->provider->send($organization, $log->mobile, $log->message, $mediaUrl);

        if ($result['success']) {
            $log->update([
                'status' => MessageLog::STATUS_SENT,
                'message_id' => $result['message_id'],
                'sent_at' => now(),
            ]);

            return;
        }

        $log->update([
            'status' => MessageLog::STATUS_FAILED,
            'error_message' => $result['error'] ?? 'Delivery failed.',
        ]);
    }

    public function normalizeMobile(string $mobile): string
    {
        return preg_replace('/[^0-9]/', '', $mobile) ?? $mobile;
    }

    /**
     * Cancel queued message logs and drop pending queue workers so nothing sends after reconnect.
     *
     * @return array{
     *     organization_id: int,
     *     queued_logs_cancelled: int,
     *     credits_refunded: int,
     *     pending_jobs_deleted: int,
     *     failed_jobs_deleted: int
     * }
     */
    public function purgeQueuedMessages(Organization $organization, bool $refundCredits = true): array
    {
        $queuedLogs = MessageLog::query()
            ->where('organization_id', $organization->id)
            ->where('status', MessageLog::STATUS_QUEUED)
            ->orderBy('id')
            ->get();

        $creditsRefunded = 0;

        DB::transaction(function () use ($organization, $queuedLogs, $refundCredits, &$creditsRefunded) {
            foreach ($queuedLogs as $log) {
                $log->update([
                    'status' => MessageLog::STATUS_FAILED,
                    'error_message' => 'Cancelled — removed from queue before send.',
                ]);

                if ($refundCredits && $log->credits_used > 0) {
                    $this->creditService->addCredits(
                        $organization,
                        $log->credits_used,
                        "Refund cancelled queued message #{$log->id}"
                    );
                    $creditsRefunded += $log->credits_used;
                }
            }
        });

        $pendingJobsDeleted = $this->deletePendingWhatsAppJobs();
        $failedJobsDeleted = $this->deleteFailedWhatsAppJobs();

        return [
            'organization_id' => $organization->id,
            'queued_logs_cancelled' => $queuedLogs->count(),
            'credits_refunded' => $creditsRefunded,
            'pending_jobs_deleted' => $pendingJobsDeleted,
            'failed_jobs_deleted' => $failedJobsDeleted,
        ];
    }

    private function deletePendingWhatsAppJobs(): int
    {
        $deleted = 0;

        DB::table('jobs')
            ->where('queue', 'messages')
            ->orderBy('id')
            ->chunkById(200, function ($jobs) use (&$deleted) {
                foreach ($jobs as $job) {
                    if ($this->isSendWhatsAppJobPayload((string) $job->payload)) {
                        DB::table('jobs')->where('id', $job->id)->delete();
                        $deleted++;
                    }
                }
            });

        return $deleted;
    }

    private function deleteFailedWhatsAppJobs(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            return 0;
        }

        $deleted = 0;

        DB::table('failed_jobs')
            ->orderBy('id')
            ->chunkById(200, function ($jobs) use (&$deleted) {
                foreach ($jobs as $job) {
                    if ($this->isSendWhatsAppJobPayload((string) $job->payload)) {
                        DB::table('failed_jobs')->where('id', $job->id)->delete();
                        $deleted++;
                    }
                }
            });

        return $deleted;
    }

    private function isSendWhatsAppJobPayload(string $payload): bool
    {
        return str_contains($payload, 'SendWhatsAppMessage');
    }

    public function getDashboardStats(Organization $organization): array
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $todayMessages = $organization->messageLogs()->where('created_at', '>=', $today)->count();
        $monthMessages = $organization->messageLogs()->where('created_at', '>=', $monthStart)->count();
        $sentCount = $organization->messageLogs()->where('status', MessageLog::STATUS_SENT)->count();
        $totalCount = $organization->messageLogs()->whereIn('status', [MessageLog::STATUS_SENT, MessageLog::STATUS_FAILED])->count();
        $failedCount = $organization->messageLogs()->where('status', MessageLog::STATUS_FAILED)->count();

        return [
            'balance' => $this->creditService->getBalance($organization),
            'connected_number' => $organization->whatsappConnection?->phone_number,
            'connection_status' => $organization->whatsappConnection?->getClientStatus() ?? 'Disconnected',
            'messages_today' => $todayMessages,
            'messages_this_month' => $monthMessages,
            'success_rate' => $totalCount > 0 ? round(($sentCount / $totalCount) * 100, 1) : 100,
            'failed_messages' => $failedCount,
        ];
    }
}
