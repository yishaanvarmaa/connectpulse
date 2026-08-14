@extends('layouts.admin')

@section('title', 'Message Logs')

@section('content')
<h1 class="text-2xl font-bold text-slate-900 mb-6">Global Message Logs</h1>

<form method="GET" class="mb-4 flex gap-3">
    <select name="organization_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">All Organizations</option>
        @foreach($organizations as $org)
            <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->company_name }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">All Statuses</option>
        @foreach(['queued', 'sent', 'failed'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Mobile</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Message</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Credits</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sent At</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($logs as $log)
                <tr>
                    <td class="px-6 py-4 text-sm text-slate-900">{{ $log->organization->company_name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $log->mobile }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">{{ $log->message }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="{{ $log->status === 'sent' ? 'text-green-600' : ($log->status === 'failed' ? 'text-red-600' : 'text-amber-600') }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $log->credits_used }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $log->sent_at?->format('M d, H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-slate-200">{{ $logs->links() }}</div>
</div>
@endsection
