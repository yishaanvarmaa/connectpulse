@extends('layouts.app')

@section('title', $lead->name)

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('org.crm.leads.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Leads</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $lead->name }}</h1>
                    <p class="mt-1 text-lg text-slate-600">{{ $lead->phone }}</p>
                    @if($lead->email)<p class="text-sm text-slate-500">{{ $lead->email }}</p>@endif
                    @if($lead->company)<p class="text-sm text-slate-500">{{ $lead->company }}@if($lead->designation) · {{ $lead->designation }}@endif</p>@endif
                </div>
                <span class="inline-flex self-start text-xs font-medium px-2.5 py-1 rounded-full bg-brand-50 text-brand-700">{{ $lead->statusLabel() }}</span>
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                @if($lead->interested_product)
                    <div><dt class="text-slate-500">Interested In</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $lead->interested_product }}</dd></div>
                @endif
                @if($lead->estimated_value)
                    <div><dt class="text-slate-500">Est. Value</dt><dd class="font-medium text-slate-900 mt-0.5">₹{{ number_format($lead->estimated_value, 0) }}</dd></div>
                @endif
                <div><dt class="text-slate-500">Source</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $lead->sourceLabel() }}</dd></div>
                <div><dt class="text-slate-500">Priority</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $lead->priorityLabel() }}</dd></div>
                @if($lead->next_follow_up_at)
                    <div><dt class="text-slate-500">Next Follow-up</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $lead->next_follow_up_at->format('d M, h:i A') }}</dd></div>
                @endif
                @if($lead->last_contacted_at)
                    <div><dt class="text-slate-500">Last Contacted</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $lead->last_contacted_at->format('d M, h:i A') }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Quick Actions --}}
        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Quick Actions</h2>
            <div class="flex flex-wrap gap-2">
                <a href="tel:{{ $lead->phone }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Call</a>
                <button type="button" onclick="document.getElementById('whatsapp-form').classList.toggle('hidden')"
                        class="inline-flex items-center rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100">WhatsApp</button>
                <button type="button" onclick="document.getElementById('followup-form').classList.toggle('hidden')"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Add Follow-up</button>
                <a href="{{ route('org.crm.leads.edit', $lead) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                @if(!$lead->isClosed())
                    <form method="POST" action="{{ route('org.crm.leads.status', $lead) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="won">
                        <button type="submit" class="inline-flex items-center rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100">Mark Won</button>
                    </form>
                    <button type="button" onclick="document.getElementById('lost-form').classList.toggle('hidden')"
                            class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">Mark Lost</button>
                @endif
            </div>

            <form id="whatsapp-form" method="POST" action="{{ route('org.crm.leads.whatsapp', $lead) }}" class="hidden mt-4 space-y-2">
                @csrf
                <textarea name="message" rows="3" required placeholder="Type your WhatsApp message..."
                          class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Send via ConnectPulse</button>
            </form>

            <form id="followup-form" method="POST" action="{{ route('org.crm.leads.follow-ups.store', $lead) }}" class="hidden mt-4 space-y-2">
                @csrf
                <input type="datetime-local" name="scheduled_at" required
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="type" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach($followUpTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="notes" rows="2" placeholder="Notes (optional)"
                          class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Schedule Follow-up</button>
            </form>

            <form id="lost-form" method="POST" action="{{ route('org.crm.leads.status', $lead) }}" class="hidden mt-4 space-y-2">
                @csrf
                <input type="hidden" name="status" value="lost">
                <input type="text" name="lost_reason" placeholder="Reason for loss (optional)"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirm Lost</button>
            </form>
        </div>

        {{-- Status change --}}
        @if(!$lead->isClosed())
        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Sales Stage</h2>
            <form method="POST" action="{{ route('org.crm.leads.status', $lead) }}" class="flex flex-wrap gap-2">
                @csrf
                @foreach($statuses as $key => $label)
                    @if(!in_array($key, ['won', 'lost']))
                        <button type="submit" name="status" value="{{ $key }}"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium border {{ $lead->status === $key ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                            {{ $label }}
                        </button>
                    @endif
                @endforeach
            </form>
        </div>
        @endif

        {{-- Notes --}}
        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-4">Notes</h2>
            @if($lead->notes)
                <div class="text-sm text-slate-700 whitespace-pre-line mb-4">{{ $lead->notes }}</div>
            @else
                <p class="text-sm text-slate-500 mb-4">No notes yet.</p>
            @endif
            <form method="POST" action="{{ route('org.crm.leads.notes', $lead) }}" class="flex gap-2">
                @csrf
                <input type="text" name="note" required placeholder="Add a note..."
                       class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Add</button>
            </form>
        </div>

        {{-- Communication History --}}
        <div class="rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="font-semibold text-slate-900">WhatsApp Communication</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($communications as $log)
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-start">
                            <p class="text-xs text-slate-500">{{ $log->created_at->format('d M, h:i A') }}</p>
                            <span class="text-xs font-medium {{ $log->status === 'sent' ? 'text-green-600' : ($log->status === 'failed' ? 'text-red-600' : 'text-amber-600') }}">{{ ucfirst($log->status) }}</span>
                        </div>
                        <p class="text-sm text-slate-700 mt-1">{{ $log->message }}</p>
                    </div>
                @empty
                    <p class="px-6 py-6 text-sm text-slate-500 text-center">No WhatsApp messages yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Follow-up History --}}
        <div class="rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="font-semibold text-slate-900">Follow-ups</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($lead->followUps as $followUp)
                    <div class="px-6 py-3">
                        <p class="text-sm font-medium text-slate-900">{{ $followUp->typeLabel() }}</p>
                        <p class="text-xs text-slate-500">{{ $followUp->scheduled_at->format('d M, h:i A') }}</p>
                        <p class="text-xs mt-1 {{ $followUp->isOverdue() ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $followUp->displayStatus() }}</p>
                    </div>
                @empty
                    <p class="px-6 py-4 text-sm text-slate-500">No follow-ups scheduled</p>
                @endforelse
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="font-semibold text-slate-900">Activity</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                @forelse($timeline as $activity)
                    <div class="relative pl-4 border-l-2 border-slate-200">
                        <p class="text-xs text-slate-500">{{ $activity->created_at->format('d M, h:i A') }}</p>
                        <p class="text-sm font-medium text-slate-900 mt-0.5">{{ $activity->title }}</p>
                        @if($activity->description)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $activity->description }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No activity yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
