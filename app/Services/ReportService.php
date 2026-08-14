<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function resolveRange(?string $period, ?string $from = null, ?string $to = null): array
    {
        if ($period === 'custom' && $from && $to) {
            return [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()];
        }

        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30d' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            '90d' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
            default => [null, null],
        };
    }

    public function getReport(Organization $organization, ?string $period = '30d', ?string $from = null, ?string $to = null): array
    {
        [$start, $end] = $this->resolveRange($period, $from, $to);

        $leadsQuery = Lead::forOrganization($organization);
        if ($start && $end) {
            $leadsQuery->whereBetween('created_at', [$start, $end]);
        }

        $totalLeads = (clone $leadsQuery)->count();
        $wonLeads = (clone $leadsQuery)->where('status', Lead::STATUS_WON)->count();
        $lostLeads = (clone $leadsQuery)->where('status', Lead::STATUS_LOST)->count();
        $closed = $wonLeads + $lostLeads;

        $openQuery = Lead::forOrganization($organization)->whereIn('status', Lead::openStatuses());
        $pipelineValue = (float) (clone $openQuery)->sum('estimated_value');

        $wonRevenueQuery = Lead::forOrganization($organization)->where('status', Lead::STATUS_WON);
        if ($start && $end) {
            $wonRevenueQuery->whereBetween('converted_at', [$start, $end]);
        }
        $wonRevenue = (float) $wonRevenueQuery->sum('estimated_value');

        $funnel = [];
        foreach (Lead::statuses() as $key => $label) {
            $count = Lead::forOrganization($organization)
                ->when($start && $end, fn ($q) => $q->whereBetween('created_at', [$start, $end]))
                ->where('status', $key)
                ->count();
            if ($count > 0 || in_array($key, Lead::openStatuses(), true)) {
                $funnel[] = ['status' => $key, 'label' => $label, 'count' => $count];
            }
        }

        $followUpBase = LeadFollowUp::forOrganization($organization);
        if ($start && $end) {
            $followUpBase->whereBetween('scheduled_at', [$start, $end]);
        }
        $due = (clone $followUpBase)->count();
        $completed = (clone $followUpBase)->where('status', LeadFollowUp::STATUS_COMPLETED)->count();
        $overdue = LeadFollowUp::forOrganization($organization)->overdue()->count();

        $revenueTrend = Lead::forOrganization($organization)
            ->where('status', Lead::STATUS_WON)
            ->when($start && $end, fn ($q) => $q->whereBetween('converted_at', [$start, $end]))
            ->select([
                DB::raw('DATE(converted_at) as day'),
                DB::raw('COUNT(*) as deals'),
                DB::raw('SUM(COALESCE(estimated_value, 0)) as revenue'),
            ])
            ->whereNotNull('converted_at')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'day' => $row->day,
                'deals' => (int) $row->deals,
                'revenue' => (float) $row->revenue,
            ])
            ->all();

        $sourceQuery = Lead::forOrganization($organization);
        if ($start && $end) {
            $sourceQuery->whereBetween('created_at', [$start, $end]);
        }
        $sourceAnalytics = $sourceQuery
            ->select([
                'source',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = '".Lead::STATUS_WON."' THEN 1 ELSE 0 END) as won"),
                DB::raw("SUM(CASE WHEN status = '".Lead::STATUS_LOST."' THEN 1 ELSE 0 END) as lost"),
                DB::raw("SUM(CASE WHEN status = '".Lead::STATUS_WON."' THEN COALESCE(estimated_value, 0) ELSE 0 END) as revenue"),
            ])
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
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
            })
            ->all();

        return [
            'period' => $period ?? 'all',
            'range_label' => $this->rangeLabel($period, $start, $end),
            'kpis' => [
                'total_leads' => $totalLeads,
                'won' => $wonLeads,
                'lost' => $lostLeads,
                'conversion_rate' => $closed > 0 ? round(($wonLeads / $closed) * 100, 1) : 0,
                'pipeline_value' => $pipelineValue,
                'won_revenue' => $wonRevenue,
            ],
            'source_analytics' => $sourceAnalytics,
            'funnel' => $funnel,
            'follow_up_performance' => [
                'due' => $due,
                'completed' => $completed,
                'overdue' => $overdue,
                'completion_rate' => $due > 0 ? round(($completed / $due) * 100, 1) : 0,
            ],
            'revenue_trend' => $revenueTrend,
        ];
    }

    private function rangeLabel(?string $period, $start, $end): string
    {
        return match ($period) {
            'today' => 'Today',
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            'custom' => $start && $end ? $start->format('M d').' – '.$end->format('M d, Y') : 'Custom',
            default => 'All time',
        };
    }
}
