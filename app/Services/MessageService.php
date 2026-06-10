<?php

namespace App\Services;

use App\Contracts\MessagingProviderInterface;
use App\Jobs\SendWhatsAppMessage;
use App\Models\MessageLog;
use App\Models\Organization;
use App\Services\Messaging\WhatsAppWebProvider;
use Illuminate\Support\Facades\DB;
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

    public function queueMessage(Organization $organization, string $mobile, string $message): MessageLog
    {
        return DB::transaction(function () use ($organization, $mobile, $message) {
            if (! $this->creditService->deductCredit($organization, "Queued message to {$mobile}")) {
                throw new \RuntimeException('Insufficient credits.');
            }

            $log = MessageLog::create([
                'organization_id' => $organization->id,
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

        $connection = $organization->whatsappConnection;
        if (! $connection?->isConnected()) {
            $log->update([
                'status' => MessageLog::STATUS_FAILED,
                'error_message' => 'WhatsApp not connected.',
            ]);

            return;
        }

        $result = $this->provider->send($organization, $log->mobile, $log->message);

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
