@extends('layouts.org')

@section('title', 'Contacts')

@section('page-title', 'Contacts')
@section('page-subtitle')
    {{ $contacts->total() }} total
@endsection

@section('content')
<div class="mb-4 flex flex-col gap-3 lg:flex-row">
    <form method="GET" class="flex flex-1 gap-2">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone..." class="cp-input flex-1">
        <select name="tag" class="cp-select w-auto" onchange="this.form.submit()">
            <option value="">All tags</option>
            @foreach($tags as $tag)
                <option value="{{ $tag->id }}" @selected(request('tag') == $tag->id)>{{ $tag->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid gap-4 lg:grid-cols-2 mb-6">
    <div class="cp-card">
        <div class="cp-card-header"><h2 class="text-sm font-semibold">Add contact</h2></div>
        <form method="POST" action="{{ route('org.contacts.store') }}" class="cp-card-body space-y-3">
            @csrf
            <input type="text" name="name" placeholder="Name" class="cp-input">
            <input type="text" name="phone" placeholder="Phone *" required class="cp-input">
            <input type="email" name="email" placeholder="Email" class="cp-input">
            <input type="text" name="tags" placeholder="Tags (comma separated)" class="cp-input">
            <button type="submit" class="cp-btn-primary w-full">Add contact</button>
        </form>
    </div>

    <div class="cp-card">
        <div class="cp-card-header"><h2 class="text-sm font-semibold">Import contacts</h2></div>
        <div class="cp-card-body space-y-4">
            <form method="POST" action="{{ route('org.contacts.import') }}" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <input type="hidden" name="import_type" value="csv">
                <input type="file" name="csv_file" accept=".csv,.txt" class="cp-input">
                <button type="submit" class="cp-btn-secondary w-full">Import CSV</button>
            </form>
            <form method="POST" action="{{ route('org.contacts.import') }}" class="space-y-2">
                @csrf
                <input type="hidden" name="import_type" value="paste">
                <textarea name="bulk_phones" rows="4" class="cp-input text-xs font-mono" placeholder="One per line: name,phone"></textarea>
                <button type="submit" class="cp-btn-secondary w-full">Import pasted numbers</button>
            </form>
        </div>
    </div>
</div>

@if($contacts->isEmpty())
    <x-ui.empty-state title="No contacts" description="Add contacts manually or import from CSV." />
@else
    <div class="grid gap-3 lg:hidden">
        @foreach($contacts as $contact)
            <div class="cp-card cp-card-body">
                <p class="font-medium text-slate-900">{{ $contact->name ?: 'Unknown' }}</p>
                <p class="text-sm text-slate-600">{{ $contact->phone }}</p>
                @if($contact->tags->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($contact->tags as $tag)
                            <span class="cp-badge bg-brand-50 text-brand-700">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="cp-table-wrap hidden lg:block">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Tags</th>
                    <th>Source</th>
                    <th>Last contacted</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($contacts as $contact)
                    <tr>
                        <td class="font-medium">{{ $contact->name ?: '—' }}</td>
                        <td>{{ $contact->phone }}</td>
                        <td>{{ $contact->email ?: '—' }}</td>
                        <td>
                            @foreach($contact->tags as $tag)
                                <span class="cp-badge bg-brand-50 text-brand-700">{{ $tag->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ ucfirst($contact->source) }}</td>
                        <td>{{ $contact->last_contacted_at?->format('M j, Y') ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $contacts->links() }}</div>
@endif
@endsection
