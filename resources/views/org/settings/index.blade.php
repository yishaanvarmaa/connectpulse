@extends('layouts.org')

@section('title', 'Settings')

@section('page-title', 'Settings')
@section('page-subtitle', $organization->company_name)

@section('content')
<div class="mb-5 flex flex-wrap gap-2">
    @foreach(['profile' => 'Profile', 'organization' => 'Organization', 'whatsapp' => 'WhatsApp', 'messaging' => 'Messaging', 'security' => 'Security', 'api' => 'API'] as $anchor => $label)
        <a href="#{{ $anchor }}" class="cp-filter-chip">{{ $label }}</a>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <div id="profile" class="cp-card cp-card-body scroll-mt-20">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Profile</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Name</dt><dd class="font-medium text-right">{{ auth()->user()->name }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd class="font-medium text-right">{{ auth()->user()->email }}</dd></div>
        </dl>
    </div>

    <div id="organization" class="cp-card cp-card-body scroll-mt-20">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Organization</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Company</dt><dd class="font-medium text-right">{{ $organization->company_name }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Contact</dt><dd class="font-medium text-right">{{ $organization->contact_person }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Email</dt><dd class="font-medium text-right">{{ $organization->email }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Mobile</dt><dd class="font-medium text-right">{{ $organization->mobile }}</dd></div>
        </dl>
    </div>

    <div id="whatsapp" class="cp-card cp-card-body scroll-mt-20">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">WhatsApp</h2>
        <p class="text-sm text-slate-600 mb-4">Manage your WhatsApp connection and view messaging status.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('org.whatsapp.index') }}" class="cp-btn-primary">Connection settings</a>
            <a href="{{ route('org.inbox.index') }}" class="cp-btn-secondary">Open inbox</a>
        </div>
    </div>

    <div id="messaging" class="cp-card cp-card-body scroll-mt-20">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Messaging</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Credits</dt><dd class="font-medium">{{ number_format($shellBalance ?? 0) }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Messages today</dt><dd class="font-medium">{{ number_format($shellMessagesToday ?? 0) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd class="font-medium">{{ ($shellWhatsAppConnected ?? false) ? 'Connected' : 'Offline' }}</dd></div>
        </dl>
        <a href="{{ route('org.recharge.index') }}" class="cp-btn-secondary mt-4 inline-flex">Manage credits</a>
    </div>

    <div id="security" class="cp-card cp-card-body scroll-mt-20">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Security</h2>
        <form method="POST" action="{{ route('org.settings.password') }}" class="space-y-4">@csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Current password</label>
                <input name="current_password" type="password" required class="cp-input">
                @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">New password</label>
                <input name="password" type="password" required class="cp-input">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Confirm password</label>
                <input name="password_confirmation" type="password" required class="cp-input">
            </div>
            <button type="submit" class="cp-btn-primary">Update password</button>
        </form>
    </div>

    <div id="api" class="cp-card cp-card-body scroll-mt-20">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">API</h2>
        <p class="text-sm text-slate-600 mb-4">For developers integrating ConnectPulse messaging.</p>
        <a href="{{ route('org.api-keys.index') }}" class="cp-btn-secondary">API keys & docs</a>
    </div>
</div>
@endsection
