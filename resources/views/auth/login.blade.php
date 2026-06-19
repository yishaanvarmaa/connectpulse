<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — ConnectPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-50">
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <div class="mx-auto h-12 w-12 rounded-xl bg-brand-600 flex items-center justify-center">
                <span class="text-white font-bold text-lg">CP</span>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">ConnectPulse</h1>
        </a>
        <p class="mt-2 text-sm text-slate-500">Centralized messaging for your business</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-xl sm:px-10">
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Sign in
                </button>
            </form>
            <p class="mt-6 text-center text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-brand-600">← Back to website</a>
            </p>
        </div>
        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('privacy') }}" class="hover:underline">Privacy</a> ·
            <a href="{{ route('terms') }}" class="hover:underline">Terms</a> ·
            <a href="{{ route('refund') }}" class="hover:underline">Refunds</a>
        </p>
        <p class="mt-3 text-center text-xs text-slate-400">
            {{ config('connectpulse.business.product_name') }} is a product of {{ config('connectpulse.business.legal_name') }}.
        </p>
    </div>
</div>
</body>
</html>
