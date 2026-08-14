@extends('layouts.auth')

@section('title', 'Sign in')

@section('auth-content')
<h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
<p class="mt-1 text-sm text-slate-500">Sign in to your sales workspace</p>

@if(session('error'))
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

@if(session('success'))
    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
        @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
        <input id="password" name="password" type="password" required
               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-[#635bff] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20">
    </div>
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input id="remember" name="remember" type="checkbox" class="rounded border-slate-300 text-[#635bff] focus:ring-[#635bff]">
        Remember me
    </label>
    <button type="submit" class="w-full rounded-xl bg-[#635bff] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-[#635bff]/25 transition hover:bg-[#5b4bff]">
        Sign in
    </button>
</form>
@endsection

@section('auth-footer')
    Don't have an account?
    <a href="{{ route('register') }}" class="font-semibold text-[#635bff] hover:underline">Create free account</a>
@endsection
