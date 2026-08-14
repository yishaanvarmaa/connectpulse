<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MessageLog;
use App\Models\Organization;
use App\Models\User;

class LeadWhatsAppService
{
    public function __construct(
        private MessageService $messageService,
        private LeadActivityService $activityService,
        private LeadService $leadService,
    ) {}

    public function send(Lead $lead, User $user, string $message): MessageLog
    {
        $organization = $lead->organization;

        $log = $this->messageService->queueMessage(
            $organization,
            $lead->phone,
            $message,
            $lead->id
        );

        $this->leadService->touchContacted($lead);

        $this->activityService->record(
            $lead,
            LeadActivity::TYPE_WHATSAPP_SENT,
            'WhatsApp message sent',
            $user,
            ['message_log_id' => $log->id, 'message' => mb_substr($message, 0, 200)]
        );

        return $log;
    }

    public function getCommunicationHistory(Lead $lead, int $limit = 20)
    {
        $normalizedPhone = $lead->normalizedPhone();

        return MessageLog::query()
            ->where('organization_id', $lead->organization_id)
            ->where(function ($q) use ($lead, $normalizedPhone) {
                $q->where('lead_id', $lead->id)
                    ->orWhere('mobile', $normalizedPhone);
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
