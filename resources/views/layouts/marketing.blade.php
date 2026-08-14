<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Manage leads, WhatsApp conversations, follow-ups and sales pipeline in one simple CRM with ConnectPulse.')">
    <title>@yield('title', 'ConnectPulse | CRM + WhatsApp for Businesses')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/marketing.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white" data-page="marketing">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
        <div class="mkt-container">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 shadow-sm">
                        <span class="text-sm font-bold text-white">CP</span>
                    </div>
                    <span class="text-lg font-bold text-slate-900">ConnectPulse</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                    <a href="{{ route('home') }}#product" class="transition hover:text-brand-600">Product</a>
                    <a href="{{ route('home') }}#follow-ups" class="transition hover:text-brand-600">Follow-ups</a>
                    <a href="{{ route('home') }}#whatsapp-api" class="transition hover:text-brand-600">WhatsApp API</a>
                    <a href="{{ route('pricing') }}" class="transition hover:text-brand-600 {{ request()->routeIs('pricing') ? 'text-brand-600' : '' }}">Pricing</a>
                    <a href="{{ route('contact') }}" class="transition hover:text-brand-600 {{ request()->routeIs('contact') ? 'text-brand-600' : '' }}">Contact</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('org.dashboard') }}" class="mkt-btn-primary !px-4 !py-2">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden text-sm font-medium text-slate-600 hover:text-slate-900 sm:inline">Sign in</a>
                        <a href="{{ route('contact') }}" class="hidden mkt-btn-primary !px-4 !py-2 sm:inline-flex">Start Free</a>
                    @endauth
                    <button id="mkt-nav-toggle" type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden" aria-expanded="false" aria-label="Menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <div id="mkt-nav-mobile" class="mkt-nav-mobile-panel pb-4">
                <nav class="flex flex-col gap-3 text-sm font-medium text-slate-700">
                    <a href="{{ route('home') }}#product">Product</a>
                    <a href="{{ route('home') }}#follow-ups">Follow-ups</a>
                    <a href="{{ route('home') }}#whatsapp-api">WhatsApp API</a>
                    <a href="{{ route('pricing') }}">Pricing</a>
                    <a href="{{ route('contact') }}">Contact</a>
                    <a href="{{ route('login') }}">Customer Login</a>
                    <a href="{{ route('contact') }}" class="mkt-btn-primary text-center">Start Free</a>
                </nav>
            </div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="bg-slate-950 text-slate-400">
        <div class="mkt-container py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600">
                            <span class="text-xs font-bold text-white">CP</span>
                        </div>
                        <span class="text-lg font-bold text-white">ConnectPulse</span>
                    </div>
                    <p class="max-w-sm text-sm leading-relaxed">
                        CRM + WhatsApp workspace for businesses that can't afford to forget a follow-up.
                    </p>
                    <p class="mt-4 text-sm">{{ config('connectpulse.business.support_email') }}</p>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Product</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}#product" class="hover:text-white">CRM</a></li>
                        <li><a href="{{ route('home') }}#product" class="hover:text-white">Leads</a></li>
                        <li><a href="{{ route('home') }}#follow-ups" class="hover:text-white">Follow-ups</a></li>
                        <li><a href="{{ route('home') }}#product" class="hover:text-white">Pipeline</a></li>
                        <li><a href="{{ route('home') }}#whatsapp" class="hover:text-white">Inbox</a></li>
                        <li><a href="{{ route('home') }}#whatsapp-api" class="hover:text-white">WhatsApp API</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Resources</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Documentation</a></li>
                        <li><a href="{{ route('home') }}#whatsapp-api" class="hover:text-white">API</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">Customer Login</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Company</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('contact') }}" class="hover:text-white">About</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white">Terms</a></li>
                        <li><a href="{{ route('refund') }}" class="hover:text-white">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-slate-800 pt-8 text-xs sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ config('connectpulse.business.legal_name') }}. All rights reserved.</p>
                @if(config('connectpulse.business.gstin'))
                    <p>GSTIN: {{ config('connectpulse.business.gstin') }}</p>
                @endif
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
