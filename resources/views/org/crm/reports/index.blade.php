@extends('layouts.app')

@section('title', 'CRM Reports')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
    <p class="mt-1 text-sm text-slate-500">Lead source performance and conversion metrics</p>
</div>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-8">
    <x-stat-card label="Total Leads" :value="number_format($stats['total_leads'])" />
    <x-stat-card label="Conversion Rate" :value="$stats['conversion_rate'].'%'" />
    <x-stat-card label="Pipeline Value" :value="'₹'.number_format($stats['pipeline_value'], 0)" />
    <x-stat-card label="Won Revenue" :value="'₹'.number_format($stats['won_revenue'], 0)" />
</div>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-900">Leads by Source</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Source</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Leads</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Won</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Lost</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Conversion</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sourceAnalytics as $row)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $row['source_label'] }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 text-right">{{ $row['total'] }}</td>
                        <td class="px-6 py-4 text-sm text-green-600 text-right">{{ $row['won'] }}</td>
                        <td class="px-6 py-4 text-sm text-red-600 text-right">{{ $row['lost'] }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 text-right">{{ $row['conversion_rate'] }}%</td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900 text-right">₹{{ number_format($row['revenue'], 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-sm text-slate-500 text-center">No data yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
