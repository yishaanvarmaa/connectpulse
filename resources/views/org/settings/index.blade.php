@extends('layouts.org')

@section('title', 'Settings')

@php $pageTitle = 'Settings'; $pageSubtitle = $organization->company_name; @endphp

@section('content')
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <div class="cp-card cp-card-body">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Organization</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Company</dt><dd class="font-medium text-right">{{ $organization->company_name }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Contact</dt><dd class="font-medium text-right">{{ $organization->contact_person }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Email</dt><dd class="font-medium text-right">{{ $organization->email }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Mobile</dt><dd class="font-medium text-right">{{ $organization->mobile }}</dd></div>
        </dl>
    </div>

    <div class="cp-card cp-card-body">
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
</div>
@endsection
