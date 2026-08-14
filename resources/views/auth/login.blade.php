<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — ConnectPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased bg-slate-950 text-slate-100">
<div class="flex min-h-full">
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between bg-gradient-to-br from-brand-900 via-brand-800 to-slate-900 p-12">
        <div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur">
                <span class="text-sm font-bold">CP</span>
            </div>
            <h1 class="mt-8 text-3xl font-semibold tracking-tight">Sales CRM + WhatsApp,<br>in one place.</h1>
            <p class="mt-4 max-w-md text-sm text-brand-100/80 leading-relaxed">Capture leads, schedule follow-ups, and communicate through WhatsApp — without switching tools.</p>
        </div>
        <p class="text-xs text-brand-200/60">Trusted by diagnostics & healthcare businesses across India.</p>
    </div>

    <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:px-16 bg-white text-slate-900">
        <div class="mx-auto w-full max-w-sm">
            <div class="lg:hidden mb-8 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white text-xs font-bold">CP</div>
                <span class="font-semibold">ConnectPulse</span>
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Welcome back</h2>
            <p class="mt-1 text-sm text-slate-500">Sign in to your workspace</p>

            @if(session('error'))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">@csrf
                <div>
                    <label for="email" class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" class="cp-input">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-1 block text-xs font-medium text-slate-600">Password</label>
                    <input id="password" name="password" type="password" required class="cp-input">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input id="remember" name="remember" type="checkbox" class="rounded border-slate-300 text-brand-600">
                    Remember me
                </label>
                <button type="submit" class="cp-btn-primary w-full py-2.5">Sign in</button>
            </form>
            <p class="mt-6 text-center text-xs text-slate-400"><a href="{{ route('home') }}" class="hover:text-brand-600">← Back to website</a></p>
        </div>
    </div>
</div>
</body>
</html>
