<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'ConnectPulse — Connect WhatsApp once, send business messages through a secure API. Built for diagnostics, healthcare, and growing businesses in India.')">
    <title>@yield('title', 'ConnectPulse') — WhatsApp Messaging API for Business</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white">
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="h-9 w-9 rounded-xl bg-brand-600 flex items-center justify-center shadow-sm">
                        <span class="text-white font-bold text-sm">CP</span>
                    </div>
                    <span class="font-bold text-slate-900 text-lg">ConnectPulse</span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="{{ route('home') }}#features" class="hover:text-brand-600 transition">Features</a>
                    <a href="{{ route('home') }}#how-it-works" class="hover:text-brand-600 transition">How it works</a>
                    <a href="{{ route('pricing') }}" class="hover:text-brand-600 transition {{ request()->routeIs('pricing') ? 'text-brand-600' : '' }}">Pricing</a>
                    <a href="{{ route('contact') }}" class="hover:text-brand-600 transition {{ request()->routeIs('contact') ? 'text-brand-600' : '' }}">Contact</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('org.dashboard') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline text-sm font-medium text-slate-600 hover:text-slate-900">Sign in</a>
                        <a href="{{ route('contact') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-300">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-brand-600 flex items-center justify-center">
                            <span class="text-white font-bold text-xs">CP</span>
                        </div>
                        <span class="font-bold text-white text-lg">ConnectPulse</span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-md leading-relaxed">
                        Centralized WhatsApp messaging platform for businesses. Connect your number once, integrate via REST API, and send notifications at scale.
                    </p>
                    <p class="mt-4 text-sm text-slate-400">
                        {{ config('connectpulse.business.support_email') }}
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Product</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Customer Login</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">Terms of Service</a></li>
                        <li><a href="{{ route('refund') }}" class="hover:text-white transition">Refund Policy</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 pt-8 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
                <div class="text-center sm:text-left">
                    <p>&copy; {{ date('Y') }} {{ config('connectpulse.business.legal_name') }}. All rights reserved.</p>
                    <p class="mt-1 text-slate-400">{{ config('connectpulse.business.product_name') }} is a product of {{ config('connectpulse.business.legal_name') }}.</p>
                </div>
                @if(config('connectpulse.business.gstin'))
                    <p>GSTIN: {{ config('connectpulse.business.gstin') }}</p>
                @endif
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
