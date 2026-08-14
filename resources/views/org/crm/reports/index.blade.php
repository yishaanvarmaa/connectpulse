@extends('layouts.org')

@section('title', 'Reports')

@php $pageTitle = 'Reports'; $pageSubtitle = 'Lead source performance'; @endphp

@section('content')
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-ui.stat label="Total leads" :value="number_format($stats['total_leads'])" />
    <x-ui.stat label="Conversion" :value="$stats['conversion_rate'].'%'" />
    <x-ui.stat label="Pipeline" :value="'₹'.number_format($stats['pipeline_value'], 0)" />
    <x-ui.stat label="Won revenue" :value="'₹'.number_format($stats['won_revenue'], 0)" />
</div>

@if(empty($sourceAnalytics))
    <x-ui.empty-state title="No report data yet" description="Reports will populate once you add leads and close deals." />
@else
    <div class="cp-table-wrap">
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
                @foreach($sourceAnalytics as $row)
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
    </div>
@endif
@endsection
