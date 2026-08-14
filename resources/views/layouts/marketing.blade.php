<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Manage leads, WhatsApp conversations, follow-ups and sales pipeline in one simple CRM with ConnectPulse.')">
    <title>@yield('title', 'ConnectPulse | CRM + WhatsApp for Businesses')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/marketing.js'])
    <style>body.mkt-page { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="mkt-page" data-page="marketing">
    <header id="mkt-nav" class="mkt-nav mkt-nav--dark">
        <div class="mkt-nav__bar">
            <div class="mkt-wrap mkt-nav__inner">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#635bff] shadow-lg shadow-[#635bff]/30">
                        <span class="text-sm font-bold text-white">CP</span>
                    </div>
                    <span class="mkt-nav__logo-text text-lg font-bold text-white">ConnectPulse</span>
                </a>

                <nav class="hidden items-center gap-7 md:flex">
                    <a href="{{ route('home') }}#product" class="mkt-nav__link">Product</a>
                    <a href="{{ route('home') }}#features" class="mkt-nav__link">Features</a>
                    <a href="{{ route('home') }}#follow-ups" class="mkt-nav__link">Follow-ups</a>
                    <a href="{{ route('home') }}#whatsapp" class="mkt-nav__link">WhatsApp</a>
                    <a href="{{ route('pricing') }}" class="mkt-nav__link {{ request()->routeIs('pricing') ? '!text-[#8b7cff]' : '' }}">Pricing</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('org.dashboard') }}" class="mkt-btn mkt-btn--primary !px-4 !py-2 text-xs sm:text-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="mkt-nav__login hidden sm:inline">Login</a>
                        <a href="{{ route('contact') }}" class="mkt-btn mkt-btn--primary hidden !px-4 !py-2 text-xs sm:inline-flex sm:text-sm">Get Started</a>
                    @endauth
                    <button id="mkt-nav-toggle" type="button" class="rounded-lg p-2 text-white/80 hover:bg-white/10 md:hidden" aria-expanded="false" aria-label="Menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <div id="mkt-nav-mobile" class="mkt-nav-mobile">
                <nav class="flex flex-col gap-3">
                    <a href="{{ route('home') }}#product" class="mkt-nav__link">Product</a>
                    <a href="{{ route('home') }}#features" class="mkt-nav__link">Features</a>
                    <a href="{{ route('home') }}#follow-ups" class="mkt-nav__link">Follow-ups</a>
                    <a href="{{ route('home') }}#whatsapp" class="mkt-nav__link">WhatsApp</a>
                    <a href="{{ route('pricing') }}" class="mkt-nav__link">Pricing</a>
                    <a href="{{ route('login') }}" class="mkt-nav__link">Login</a>
                    <a href="{{ route('contact') }}" class="mkt-btn mkt-btn--primary mt-2 text-center">Get Started</a>
                </nav>
            </div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="mkt-footer">
        <div class="mkt-wrap py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#635bff]">
                            <span class="text-xs font-bold text-white">CP</span>
                        </div>
                        <span class="text-lg font-bold text-white">ConnectPulse</span>
                    </div>
                    <p class="max-w-sm text-sm leading-relaxed text-slate-500">
                        The sales workspace for businesses that sell through conversations. Leads, WhatsApp, follow-ups and pipeline — unified.
                    </p>
                    <p class="mt-4 text-sm text-slate-400">{{ config('connectpulse.business.support_email') }}</p>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-600">Product</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('home') }}#product" class="hover:text-white transition">CRM</a></li>
                        <li><a href="{{ route('home') }}#features" class="hover:text-white transition">Leads</a></li>
                        <li><a href="{{ route('home') }}#follow-ups" class="hover:text-white transition">Follow-ups</a></li>
                        <li><a href="{{ route('home') }}#pipeline" class="hover:text-white transition">Pipeline</a></li>
                        <li><a href="{{ route('home') }}#whatsapp" class="hover:text-white transition">Inbox</a></li>
                        <li><a href="{{ route('home') }}#whatsapp-api" class="hover:text-white transition">WhatsApp API</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-600">Resources</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Documentation</a></li>
                        <li><a href="{{ route('home') }}#whatsapp-api" class="hover:text-white transition">API</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Customer Login</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-600">Company</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">About</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">Terms</a></li>
                        <li><a href="{{ route('refund') }}" class="hover:text-white transition">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-white/5 pt-8 text-xs text-slate-600 sm:flex-row">
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
