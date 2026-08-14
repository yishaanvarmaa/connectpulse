<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Organization;
use Illuminate\Support\Collection;

class CommandCenterService
{
    public function __construct(
        private CrmDashboardService $crmDashboardService,
        private FollowUpService $followUpService,
        private MessageService $messageService,
    ) {}

    public function getHomeData(Organization $organization): array
    {
        $crmStats = $this->crmDashboardService->getStats($organization);
        $groups = $this->followUpService->getDashboardGroups($organization);
        $messagingStats = $this->messageService->getDashboardStats($organization);
        $pipelineSummary = $this->getPipelineSummary($organization);
        $newLeadsCount = Lead::forOrganization($organization)
            ->where('status', Lead::STATUS_NEW)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $demosToday = Lead::forOrganization($organization)
            ->where('status', Lead::STATUS_DEMO_SCHEDULED)
            ->whereDate('next_follow_up_at', today())
            ->count();

        return [
            'crmStats' => $crmStats,
            'followUpGroups' => $groups,
            'messagingStats' => $messagingStats,
            'pipelineSummary' => $pipelineSummary,
            'newLeadsCount' => $newLeadsCount,
            'demosToday' => $demosToday,
            'nextAction' => $this->getNextAction($organization, $groups),
            'recentLeads' => Lead::forOrganization($organization)->latest()->limit(5)->get(),
            'recentMessages' => $organization->messageLogs()->latest()->limit(5)->get(),
        ];
    }

    public function getNextAction(Organization $organization, ?array $groups = null): ?array
    {
        $groups ??= $this->followUpService->getDashboardGroups($organization);

        /** @var LeadFollowUp|null $overdue */
        $overdue = $groups['overdue']->sortByDesc(fn (LeadFollowUp $f) => $f->lead->estimated_value ?? 0)->first();
        if ($overdue) {
            return $this->formatFollowUpAction($overdue, 'overdue');
        }

        /** @var LeadFollowUp|null $today */
        $today = $groups['today']->sortBy('scheduled_at')->first();
        if ($today) {
            return $this->formatFollowUpAction($today, 'today');
        }

        $demoToday = Lead::forOrganization($organization)
            ->where('status', Lead::STATUS_DEMO_SCHEDULED)
            ->whereDate('next_follow_up_at', today())
            ->orderBy('next_follow_up_at')
            ->first();

        if ($demoToday) {
            return [
                'type' => 'demo',
                'label' => 'Demo today',
                'lead' => $demoToday,
                'followUp' => null,
                'time' => $demoToday->next_follow_up_at,
                'subtitle' => $demoToday->interested_product,
                'value' => $demoToday->estimated_value,
            ];
        }

        $newLead = Lead::forOrganization($organization)
            ->where('status', Lead::STATUS_NEW)
            ->orderByDesc('created_at')
            ->first();

        if ($newLead) {
            return [
                'type' => 'new_lead',
                'label' => 'New lead',
                'lead' => $newLead,
                'followUp' => null,
                'time' => $newLead->created_at,
                'subtitle' => 'Just added · '.$newLead->sourceLabel(),
                'value' => $newLead->estimated_value,
            ];
        }

        /** @var LeadFollowUp|null $upcoming */
        $upcoming = $groups['upcoming']->sortBy('scheduled_at')->first();
        if ($upcoming) {
            return $this->formatFollowUpAction($upcoming, 'upcoming');
        }

        return null;
    }

    public function getPipelineSummary(Organization $organization): array
    {
        $leads = Lead::forOrganization($organization)
            ->whereIn('status', Lead::openStatuses())
            ->get();

        $byStatus = [];
        foreach (Lead::openStatuses() as $status) {
            $column = $leads->where('status', $status);
            $byStatus[$status] = [
                'status' => $status,
                'label' => Lead::statuses()[$status] ?? $status,
                'count' => $column->count(),
                'value' => (float) $column->sum(fn (Lead $l) => (float) ($l->estimated_value ?? 0)),
            ];
        }

        return [
            'total_value' => (float) $leads->sum(fn (Lead $l) => (float) ($l->estimated_value ?? 0)),
            'open_count' => $leads->count(),
            'stages' => $byStatus,
        ];
    }

    private function formatFollowUpAction(LeadFollowUp $followUp, string $urgency): array
    {
        $lead = $followUp->lead;
        $daysOverdue = $urgency === 'overdue'
            ? (int) $followUp->scheduled_at->diffInDays(now())
            : 0;

        return [
            'type' => 'follow_up',
            'urgency' => $urgency,
            'label' => match ($urgency) {
                'overdue' => 'Follow-up overdue',
                'today' => 'Follow-up today',
                default => 'Next follow-up',
            },
            'lead' => $lead,
            'followUp' => $followUp,
            'time' => $followUp->scheduled_at,
            'subtitle' => $lead->interested_product ?? $followUp->typeLabel(),
            'value' => $lead->estimated_value,
            'days_overdue' => $daysOverdue,
        ];
    }
}
