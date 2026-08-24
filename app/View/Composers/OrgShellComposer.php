<?php

namespace App\View\Composers;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Services\CreditService;
use App\Services\MessageService;
use Illuminate\View\View;

class OrgShellComposer
{
    public function __construct(
        private CreditService $creditService,
        private MessageService $messageService,
    ) {}

    public function compose(View $view): void
    {
        try {
            $user = auth()->user();

            if (! $user?->isOrganizationAdmin() || ! $user->organization) {
                return;
            }

            $organization = $user->organization->loadMissing('whatsappConnection', 'creditWallet');

            try {
                $stats = $this->messageService->getDashboardStats($organization);
            } catch (\Throwable) {
                $stats = [
                    'connection_status' => 'Disconnected',
                    'connected_number' => $organization->whatsappConnection?->phone_number,
                    'messages_today' => 0,
                    'messages_this_month' => 0,
                ];
            }

            $overdueCount = 0;
            $todayCount = 0;
            $newLeadsCount = 0;
            $balance = 0;

            try {
                $balance = $this->creditService->getBalance($organization);
                $overdueCount = LeadFollowUp::forOrganization($organization)->overdue()->count();
                $todayCount = LeadFollowUp::forOrganization($organization)->dueToday()->count();
                $newLeadsCount = Lead::forOrganization($organization)
                    ->where('status', Lead::STATUS_NEW)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
            } catch (\Throwable) {
                // Keep the org shell rendering even if notification queries fail.
            }

            $view->with([
                'shellOrganization' => $organization,
                'shellBalance' => $balance,
                'shellWhatsAppConnected' => ($stats['connection_status'] ?? '') === 'Connected',
                'shellWhatsAppPhone' => $stats['connected_number'] ?? null,
                'shellMessagesToday' => $stats['messages_today'] ?? 0,
                'shellMessagesMonth' => $stats['messages_this_month'] ?? 0,
                'shellNotificationCount' => $overdueCount + $todayCount,
                'shellNotifications' => array_values(array_filter([
                    $overdueCount > 0 ? ['type' => 'danger', 'label' => "{$overdueCount} overdue follow-up".($overdueCount > 1 ? 's' : ''), 'url' => route('org.crm.follow-ups.index')] : null,
                    $todayCount > 0 ? ['type' => 'warning', 'label' => "{$todayCount} follow-up".($todayCount > 1 ? 's' : '').' today', 'url' => route('org.crm.follow-ups.index')] : null,
                    $newLeadsCount > 0 ? ['type' => 'info', 'label' => "{$newLeadsCount} new lead".($newLeadsCount > 1 ? 's' : ''), 'url' => route('org.crm.leads.index', ['view' => 'new'])] : null,
                ])),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
