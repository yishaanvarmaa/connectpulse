@extends('layouts.org')

@section('title', 'More')

@section('page-title', 'More')
@section('page-subtitle', 'Pipeline, reports & settings')

@section('content')
<div class="grid gap-3 sm:grid-cols-2">
    @foreach([
        ['Follow-ups', 'Today\'s tasks & overdue', route('org.crm.follow-ups.index'), 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Pipeline', 'Track deals by stage', route('org.crm.pipeline.index'), 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7'],
        ['Reports', 'Revenue & conversion', route('org.crm.reports.index'), 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['WhatsApp', 'Connection & status', route('org.whatsapp.index'), 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        ['Credits', 'Balance & usage', route('org.recharge.index'), 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ['API', 'Keys & documentation', route('org.api-keys.index'), 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
        ['Settings', 'Profile & security', route('org.settings.index'), 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
        ['Search', 'Find anything fast', route('org.search'), 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
    ] as [$title, $desc, $url, $path])
        <a href="{{ $url }}" class="cp-card cp-card-body flex items-center gap-4 hover:border-brand-200">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900">{{ $title }}</p>
                <p class="text-xs text-slate-500">{{ $desc }}</p>
            </div>
        </a>
    @endforeach
</div>
@endsection
