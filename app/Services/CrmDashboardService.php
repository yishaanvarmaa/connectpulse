<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class CrmDashboardService
{
    public function getStats(Organization $organization): array
    {
        $leads = Lead::forOrganization($organization);

        $totalLeads = (clone $leads)->count();
        $openLeads = (clone $leads)->whereIn('status', Lead::openStatuses())->count();
        $wonLeads = (clone $leads)->where('status', Lead::STATUS_WON)->count();
        $lostLeads = (clone $leads)->where('status', Lead::STATUS_LOST)->count();
        $newLeads = (clone $leads)->where('status', Lead::STATUS_NEW)->count();

        $closedLeads = $wonLeads + $lostLeads;
        $conversionRate = $closedLeads > 0 ? round(($wonLeads / $closedLeads) * 100, 1) : 0;

        $pipelineValue = (clone $leads)
            ->whereIn('status', Lead::openStatuses())
            ->sum('estimated_value');

        $wonRevenue = (clone $leads)
            ->where('status', Lead::STATUS_WON)
            ->sum('estimated_value');

        $followUpsToday = LeadFollowUp::forOrganization($organization)->dueToday()->count();
        $overdueFollowUps = LeadFollowUp::forOrganization($organization)->overdue()->count();

        return [
            'total_leads' => $totalLeads,
            'open_leads' => $openLeads,
            'new_leads' => $newLeads,
            'follow_ups_today' => $followUpsToday,
            'overdue_follow_ups' => $overdueFollowUps,
            'won' => $wonLeads,
            'lost' => $lostLeads,
            'conversion_rate' => $conversionRate,
            'pipeline_value' => (float) $pipelineValue,
            'won_revenue' => (float) $wonRevenue,
        ];
    }

    public function getSourceAnalytics(Organization $organization): array
    {
        $rows = Lead::forOrganization($organization)
            ->select([
                'source',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = '".Lead::STATUS_WON."' THEN 1 ELSE 0 END) as won"),
                DB::raw("SUM(CASE WHEN status = '".Lead::STATUS_LOST."' THEN 1 ELSE 0 END) as lost"),
                DB::raw("SUM(CASE WHEN status = '".Lead::STATUS_WON."' THEN COALESCE(estimated_value, 0) ELSE 0 END) as revenue"),
            ])
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        return $rows->map(function ($row) {
            $closed = $row->won + $row->lost;

            return [
                'source' => $row->source,
                'source_label' => Lead::sources()[$row->source] ?? ucfirst($row->source),
                'total' => (int) $row->total,
                'won' => (int) $row->won,
                'lost' => (int) $row->lost,
                'conversion_rate' => $closed > 0 ? round(($row->won / $closed) * 100, 1) : 0,
                'revenue' => (float) $row->revenue,
            ];
        })->all();
    }
}
