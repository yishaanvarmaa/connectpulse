@extends('layouts.auth')

@section('title', 'Sign up')
@section('brand-headline')
    Start closing more deals today.
@endsection
@section('brand-sub')
    Create your workspace in minutes. Add leads, connect WhatsApp, and never miss a follow-up again.
@endsection

@section('auth-content')
<h2 class="text-2xl font-bold text-slate-900">Create your account</h2>
<p class="mt-1 text-sm text-slate-500">Free to start — set up your sales workspace</p>

<form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label for="company_name" class="mb-1.5 block text-sm font-medium text-slate-700">Business name</label>
        <input id="company_name" name="company_name" type="text" required value="{{ old('company_name') }}"
               placeholder="ABC Diagnostics"
               class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
        @error('company_name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="contact_person" class="mb-1.5 block text-sm font-medium text-slate-700">Your name</label>
        <input id="contact_person" name="contact_person" type="text" required value="{{ old('contact_person') }}"
               class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
        @error('contact_person')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Work email</label>
        <input id="email" name="email" type="email" required value="{{ old('email') }}"
               class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
        @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="mobile" class="mb-1.5 block text-sm font-medium text-slate-700">Mobile</label>
        <input id="mobile" name="mobile" type="tel" required value="{{ old('mobile') }}"
               placeholder="9876543210"
               class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
        @error('mobile')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
        <input id="password" name="password" type="password" required
               class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
        @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required
               class="block w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
    </div>
    <p class="text-xs leading-relaxed text-slate-500">
        By signing up you agree to our
        <a href="{{ route('terms') }}" class="text-[#635bff] hover:underline">Terms</a> and
        <a href="{{ route('privacy') }}" class="text-[#635bff] hover:underline">Privacy Policy</a>.
    </p>
    <button type="submit" class="w-full rounded-xl bg-[#635bff] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-[#635bff]/25 transition hover:bg-[#5b4bff]">
        Create account
    </button>
</form>
@endsection

@section('auth-footer')
    Already have an account?
    <a href="{{ route('login') }}" class="font-semibold text-[#635bff] hover:underline">Sign in</a>
@endsection
