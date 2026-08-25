@extends('layouts.org')

@section('title', 'Reports')

@section('page-title', 'Reports')
@section('page-subtitle', $report['range_label'])

@section('content')
{{-- Date range --}}
<div class="mb-5 -mx-1 overflow-x-auto scrollbar-thin">
    <div class="flex min-w-max gap-2 px-1 pb-1">
        @foreach(['today' => 'Today', '7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', 'all' => 'All time'] as $key => $label)
            <a href="{{ route('org.crm.reports.index', array_merge(request()->except('period', 'page'), ['period' => $key])) }}"
               class="cp-filter-chip {{ ($period ?? '30d') === $key ? 'cp-filter-chip-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- KPIs --}}
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
    <x-ui.stat label="Leads" :value="number_format($report['kpis']['total_leads'])" />
    <x-ui.stat label="Won" :value="number_format($report['kpis']['won'])" />
    <x-ui.stat label="Lost" :value="number_format($report['kpis']['lost'])" />
    <x-ui.stat label="Conversion" :value="$report['kpis']['conversion_rate'].'%'" />
    <x-ui.stat label="Pipeline" :value="'₹'.number_format($report['kpis']['pipeline_value'], 0)" />
    <x-ui.stat label="Revenue" :value="'₹'.number_format($report['kpis']['won_revenue'], 0)" />
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    {{-- Sales funnel --}}
    <div class="cp-card">
        <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Sales funnel</h2></div>
        <div class="cp-card-body space-y-2 pt-0">
            @php $maxFunnel = max(1, collect($report['funnel'])->max('count') ?? 1); @endphp
            @forelse($report['funnel'] as $stage)
                <div>
                    <div class="mb-1 flex justify-between text-xs">
                        <span class="font-medium text-slate-700">{{ $stage['label'] }}</span>
                        <span class="text-slate-500">{{ $stage['count'] }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-brand-500" style="width: {{ max(4, ($stage['count'] / $maxFunnel) * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No funnel data yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Follow-up performance --}}
    <div class="cp-card">
        <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Follow-up performance</h2></div>
        <div class="cp-card-body pt-0">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-lg bg-slate-50 p-3 text-center">
                    <p class="text-[10px] font-medium uppercase text-slate-500">Due</p>
                    <p class="text-xl font-bold text-slate-900">{{ $report['follow_up_performance']['due'] }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3 text-center">
                    <p class="text-[10px] font-medium uppercase text-emerald-700">Completed</p>
                    <p class="text-xl font-bold text-emerald-900">{{ $report['follow_up_performance']['completed'] }}</p>
                </div>
                <div class="rounded-lg bg-red-50 p-3 text-center">
                    <p class="text-[10px] font-medium uppercase text-red-600">Overdue now</p>
                    <p class="text-xl font-bold text-red-900">{{ $report['follow_up_performance']['overdue'] }}</p>
                </div>
                <div class="rounded-lg bg-brand-50 p-3 text-center">
                    <p class="text-[10px] font-medium uppercase text-brand-700">Completion</p>
                    <p class="text-xl font-bold text-brand-900">{{ $report['follow_up_performance']['completion_rate'] }}%</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Revenue trend --}}
@if(!empty($report['revenue_trend']))
    <div class="cp-card mt-5">
        <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Revenue trend</h2></div>
        <div class="cp-card-body pt-0">
            <div class="space-y-2">
                @php $maxRev = max(1, collect($report['revenue_trend'])->max('revenue') ?? 1); @endphp
                @foreach($report['revenue_trend'] as $row)
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-20 shrink-0 text-slate-500">{{ \Carbon\Carbon::parse($row['day'])->format('M d') }}</span>
                        <div class="flex-1">
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ max(4, ($row['revenue'] / $maxRev) * 100) }}%"></div>
                            </div>
                        </div>
                        <span class="w-24 shrink-0 text-right font-medium text-slate-700">₹{{ number_format($row['revenue'], 0) }}</span>
                        <span class="w-12 shrink-0 text-right text-slate-400">{{ $row['deals'] }} won</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- Lead sources --}}
<div class="cp-table-wrap mt-5">
    <div class="cp-card-header border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-900">Lead sources</h2>
    </div>
    @if(empty($report['source_analytics']))
        <div class="px-5 py-10 text-center text-sm text-slate-500">No source data for this period.</div>
    @else
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th class="text-right">Leads</th>
                    <th class="text-right">Won</th>
                    <th class="text-right">Lost</th>
                    <th class="text-right">Conversion</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($report['source_analytics'] as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="font-medium text-slate-900">{{ $row['source_label'] }}</td>
                        <td class="text-right">{{ $row['total'] }}</td>
                        <td class="text-right text-emerald-600">{{ $row['won'] }}</td>
                        <td class="text-right text-red-600">{{ $row['lost'] }}</td>
                        <td class="text-right">{{ $row['conversion_rate'] }}%</td>
                        <td class="text-right font-semibold">₹{{ number_format($row['revenue'], 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
