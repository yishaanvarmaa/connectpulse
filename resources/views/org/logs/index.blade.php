@extends('layouts.org')

@section('title', 'Message Logs')

@php $pageTitle = 'Inbox / Logs'; $pageSubtitle = 'Communication history'; @endphp

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search mobile or message..." class="cp-input max-w-xs">
    <select name="status" class="cp-select w-auto" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(['queued', 'sent', 'failed'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
</form>

<div class="cp-table-wrap">
    <table class="cp-table">
        <thead>
            <tr>
                <th>Recipient</th>
                <th>Message</th>
                <th>Status</th>
                <th>Credits</th>
                <th>Sent</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
                <tr class="hover:bg-slate-50/80">
                    <td class="font-medium text-slate-900">{{ $log->mobile }}</td>
                    <td class="max-w-md truncate text-slate-600">{{ $log->message }}</td>
                    <td><x-ui.badge :type="$log->status">{{ strtoupper($log->status) }}</x-ui.badge></td>
                    <td>{{ $log->credits_used }}</td>
                    <td class="text-slate-500">{{ $log->sent_at?->format('M d, H:i') ?? $log->created_at->format('M d, H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-12 text-center text-sm text-slate-500">No messages yet. Messages sent via API or CRM will appear here.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="border-t border-slate-100 px-4 py-3">{{ $logs->links() }}</div>
</div>
@endsection
