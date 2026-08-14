@extends('layouts.org')

@section('title', 'Search')

@section('page-title', 'Search')
@section('page-subtitle', 'Leads, messages & follow-ups')

@section('content')
<form method="GET" action="{{ route('org.search') }}" class="mb-5">
    <input type="search" name="q" value="{{ $query }}" autofocus placeholder="Name, phone, company, product..." class="cp-input text-base">
</form>

@if(strlen($query) < 2)
    <p class="text-sm text-slate-500">Type at least 2 characters to search.</p>
@else
    @if($leads->isNotEmpty())
        <section class="mb-6">
            <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Leads</h2>
            <div class="space-y-2">
                @foreach($leads as $lead)
                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-card cp-card-body flex items-center gap-3">
                        <x-ui.avatar :name="$lead->name" />
                        <div>
                            <p class="font-medium text-slate-900">{{ $lead->name }}</p>
                            <p class="text-xs text-slate-500">{{ $lead->phone }} · {{ $lead->statusLabel() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($followUps->isNotEmpty())
        <section class="mb-6">
            <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Follow-ups</h2>
            <div class="space-y-2">
                @foreach($followUps as $fu)
                    <a href="{{ route('org.crm.leads.show', $fu->lead) }}" class="cp-card cp-card-body">
                        <p class="font-medium">{{ $fu->lead->name }}</p>
                        <p class="text-xs text-slate-500">{{ $fu->scheduled_at->format('M d, h:i A') }} · {{ $fu->typeLabel() }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($messages->isNotEmpty())
        <section class="mb-6">
            <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Messages</h2>
            <div class="space-y-2">
                @foreach($messages as $msg)
                    <a href="{{ route('org.inbox.index', ['with' => $msg->mobile]) }}" class="cp-card cp-card-body">
                        <p class="text-xs text-slate-400">{{ $msg->mobile }} · {{ $msg->created_at->format('M d') }}</p>
                        <p class="mt-1 text-sm text-slate-700">{{ Str::limit($msg->message, 80) }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($leads->isEmpty() && $followUps->isEmpty() && $messages->isEmpty())
        <p class="text-sm text-slate-500">No results for "{{ $query }}"</p>
    @endif
@endif
@endsection
