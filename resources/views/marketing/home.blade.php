@extends('layouts.marketing')

@section('title', 'ConnectPulse | CRM + WhatsApp for Businesses')
@section('meta_description', 'Manage leads, WhatsApp conversations, follow-ups and sales pipeline in one simple CRM with ConnectPulse. Never lose a lead because you forgot to follow up.')

@section('content')
{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-50 via-white to-white">
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -right-24 top-0 h-96 w-96 rounded-full bg-brand-100/60 blur-3xl"></div>
        <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-indigo-100/50 blur-3xl"></div>
    </div>
    <div class="mkt-container py-16 sm:py-20 lg:py-28">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="mkt-reveal">
                <p class="mkt-eyebrow">ConnectPulse</p>
                <h1 class="mt-4 text-4xl font-extrabold leading-[1.05] tracking-tight text-slate-900 sm:text-5xl lg:text-[3.25rem]">
                    Never lose a lead<br>because you forgot<br>to follow up.
                </h1>
                <p class="mt-6 text-lg leading-relaxed text-slate-600">
                    ConnectPulse brings your leads, WhatsApp conversations, follow-ups and sales pipeline into one simple workspace.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('contact') }}" class="mkt-btn-primary">Start Free</a>
                    <a href="#how-it-works" class="mkt-btn-secondary">See How It Works</a>
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['CRM', 'WhatsApp', 'Follow-ups', 'Sales Pipeline'] as $tag)
                        <span class="mkt-pill">{{ $tag }}</span>
                    @endforeach
                </div>
                <p class="mt-8 text-sm text-slate-500">
                    From the first enquiry to the final sale, ConnectPulse keeps every lead moving.
                </p>
            </div>
            <div class="relative lg:pl-4">
                <x-marketing.mockups.hero-dashboard />
            </div>
        </div>
    </div>
</section>

{{-- PROBLEM --}}
<section class="mkt-section bg-white">
    <div class="mkt-container">
        <div class="mx-auto max-w-3xl text-center mkt-reveal">
            <h2 class="mkt-headline">Your leads are coming in.<br>But are you following up with all of them?</h2>
        </div>
        <div class="mt-12 grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="mkt-reveal">
                <p class="text-sm font-semibold text-slate-900 mb-4">Leads arrive from everywhere</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Instagram', 'Facebook', 'WhatsApp', 'Website', 'Referrals', 'Phone calls'] as $source)
                        <span class="mkt-pill">{{ $source }}</span>
                    @endforeach
                </div>
                <div class="mt-8 space-y-1">
                    @foreach(['Lead comes in', 'You reply', 'You get busy', 'You forget', 'Customer goes somewhere else'] as $i => $step)
                        <div class="mkt-flow-step {{ $i === 4 ? 'border-red-200 bg-red-50 text-red-800' : '' }}">{{ $step }}</div>
                        @if($i < 4)<div class="mkt-flow-arrow">↓</div>@endif
                    @endforeach
                </div>
            </div>
            <div class="mkt-reveal">
                <p class="text-sm font-semibold text-brand-600 mb-4">With ConnectPulse</p>
                <div class="space-y-1">
                    @foreach(['Lead captured', 'Follow-up scheduled', 'WhatsApp conversation', 'Next action', 'Deal won'] as $i => $step)
                        <div class="mkt-flow-step border-brand-200 bg-brand-50/50">{{ $step }}</div>
                        @if($i < 4)<div class="mkt-flow-arrow text-brand-300">↓</div>@endif
                    @endforeach
                </div>
                <p class="mt-6 text-sm text-slate-600 leading-relaxed">
                    ConnectPulse combines CRM and WhatsApp communication so businesses can capture leads, follow up on time, communicate with customers and close more sales.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- PRODUCT --}}
<section id="product" class="mkt-section bg-slate-50">
    <div class="mkt-container">
        <div class="mx-auto max-w-3xl text-center mkt-reveal">
            <p class="mkt-eyebrow">Product</p>
            <h2 class="mt-3 mkt-headline">One workspace for your entire sales process.</h2>
        </div>
        <div class="mt-14 grid gap-8 lg:grid-cols-2">
            @foreach([
                ['Leads', 'Capture and organize every opportunity.', 'hero-dashboard'],
                ['Follow-ups', 'Never forget who needs a call, message or demo.', 'followups'],
                ['WhatsApp', 'Message leads directly from your CRM.', 'inbox'],
                ['Pipeline', 'See exactly where every opportunity stands.', 'pipeline'],
            ] as [$title, $desc, $mockup])
                <div class="mkt-reveal">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-1 text-slate-600">{{ $desc }}</p>
                    </div>
                    @if($mockup === 'hero-dashboard')
                        <x-marketing.mockups.hero-dashboard />
                    @elseif($mockup === 'followups')
                        <x-marketing.mockups.followups />
                    @elseif($mockup === 'inbox')
                        <x-marketing.mockups.inbox />
                    @else
                        <x-marketing.mockups.pipeline />
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- COMMAND CENTER --}}
<section class="mkt-section bg-white">
    <div class="mkt-container">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="mkt-reveal">
                <p class="mkt-eyebrow">Sales Command Center</p>
                <h2 class="mt-3 mkt-headline">Open ConnectPulse.<br>Know what to do next.</h2>
                <p class="mkt-subhead">
                    Your sales dashboard should not just show numbers. It should tell you what needs your attention.
                </p>
                <ul class="mt-8 space-y-3 text-sm text-slate-700">
                    @foreach(['Today\'s follow-ups', 'Overdue leads', 'New leads', 'Next best action', 'Pipeline value', 'Won revenue'] as $item)
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs text-brand-700">✓</span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="mkt-reveal">
                <x-marketing.mockups.hero-dashboard />
            </div>
        </div>
    </div>
</section>

{{-- FOLLOW-UPS --}}
<section id="follow-ups" class="mkt-section bg-slate-50">
    <div class="mkt-container">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="order-2 lg:order-1 mkt-reveal">
                <x-marketing.mockups.followups />
            </div>
            <div class="order-1 lg:order-2 mkt-reveal">
                <p class="mkt-eyebrow">Follow-ups</p>
                <h2 class="mt-3 mkt-headline">The follow-up you don't forget is the sale you don't miss.</h2>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['Overdue', 'Today', 'Upcoming'] as $tab)
                        <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $tab }}</span>
                    @endforeach
                </div>
                <p class="mt-6 text-slate-600 leading-relaxed">
                    Schedule a follow-up when you create or update a lead. When it's time, ConnectPulse puts it in front of you. Call. WhatsApp. Complete. Reschedule.
                </p>
                <p class="mt-4 text-sm font-medium text-slate-800">
                    No spreadsheets. No notebooks. No "I'll remember later."
                </p>
            </div>
        </div>
    </div>
</section>

{{-- WHATSAPP --}}
<section id="whatsapp" class="mkt-section bg-white">
    <div class="mkt-container">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="mkt-reveal">
                <p class="mkt-eyebrow">WhatsApp + CRM</p>
                <h2 class="mt-3 mkt-headline">Your CRM and WhatsApp.<br>Finally in the same place.</h2>
                <p class="mkt-subhead">
                    Open a lead. See their information. See the conversation. Send a WhatsApp message. Schedule the next follow-up — all without jumping between different tools.
                </p>
            </div>
            <div class="mkt-reveal">
                <x-marketing.mockups.inbox />
            </div>
        </div>
    </div>
</section>

{{-- PIPELINE --}}
<section class="mkt-section bg-slate-50">
    <div class="mkt-container">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="order-2 lg:order-1 mkt-reveal">
                <x-marketing.mockups.pipeline />
            </div>
            <div class="order-1 lg:order-2 mkt-reveal">
                <p class="mkt-eyebrow">Pipeline</p>
                <h2 class="mt-3 mkt-headline">See your sales pipeline at a glance.</h2>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach(['New', 'Contacted', 'Interested', 'Follow-up', 'Demo', 'Negotiation', 'Won'] as $stage)
                        <span class="mkt-pill">{{ $stage }}</span>
                    @endforeach
                </div>
                <p class="mt-6 text-slate-600 leading-relaxed">
                    Know exactly how many opportunities you have and how much revenue is sitting in your pipeline.
                </p>
                <p class="mt-4 text-3xl font-bold text-slate-900">₹1.24L <span class="text-base font-normal text-slate-500">open pipeline</span></p>
            </div>
        </div>
    </div>
</section>

{{-- INDUSTRIES --}}
<section class="mkt-section bg-white">
    <div class="mkt-container">
        <div class="mx-auto max-w-3xl text-center mkt-reveal">
            <h2 class="mkt-headline">If your business gets leads,<br>ConnectPulse can help you close them.</h2>
        </div>
        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Diagnostics & Clinics', 'Track enquiries, appointments and follow-ups.'],
                ['Agencies', 'Manage leads from multiple campaigns.'],
                ['SaaS & Software', 'Track demos, trials and sales.'],
                ['Service Businesses', 'Keep every enquiry moving toward a sale.'],
                ['Small Business', 'Replace spreadsheets and scattered WhatsApp chats.'],
                ['Meta & Instagram Ads', 'Turn ad leads into organized follow-ups.'],
            ] as [$title, $desc])
                <div class="mkt-card mkt-reveal">
                    <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- AD TO SALE --}}
<section class="mkt-section bg-gradient-to-br from-slate-900 to-slate-800 text-white">
    <div class="mkt-container">
        <div class="mx-auto max-w-3xl text-center mkt-reveal">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Turn ad leads into actual sales.</h2>
            <p class="mt-4 text-slate-300">Your ads bring the lead. ConnectPulse helps you follow through.</p>
        </div>
        <div class="mt-12 flex flex-wrap items-center justify-center gap-2 sm:gap-3 mkt-reveal">
            @foreach(['Meta / Instagram Ad', 'New Lead', 'ConnectPulse', 'WhatsApp', 'Follow-up', 'Demo', 'Negotiation', 'Won'] as $i => $step)
                <span class="rounded-lg bg-white/10 px-3 py-2 text-xs font-medium sm:text-sm">{{ $step }}</span>
                @if($i < 7)<span class="text-slate-500 hidden sm:inline">→</span>@endif
            @endforeach
        </div>
    </div>
</section>

{{-- MOBILE --}}
<section class="mkt-section bg-slate-50">
    <div class="mkt-container">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="mkt-reveal">
                <p class="mkt-eyebrow">Mobile CRM</p>
                <h2 class="mt-3 mkt-headline">Your sales desk.<br>In your pocket.</h2>
                <p class="mkt-subhead">
                    Check today's follow-ups. Message a lead. Call a customer. Update a deal — from your phone.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['Home', 'Lead', 'Follow-up', 'Inbox'] as $screen)
                        <span class="mkt-pill">{{ $screen }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-center mkt-reveal">
                <x-marketing.mockups.mobile-home />
            </div>
        </div>
    </div>
</section>

{{-- WHATSAPP API --}}
<section id="whatsapp-api" class="mkt-section bg-white">
    <div class="mkt-container">
        <div class="mx-auto max-w-3xl text-center mkt-reveal">
            <p class="mkt-eyebrow">For developers</p>
            <h2 class="mt-3 mkt-headline">Need WhatsApp automation too?</h2>
            <p class="mt-4 text-slate-600 leading-relaxed">
                ConnectPulse also provides a simple WhatsApp API for applications that need to send report notifications, appointment reminders, payment receipts, registration messages, transactional notifications and bulk messages.
            </p>
            <p class="mt-4 text-sm text-slate-500">
                Connect your WhatsApp Business number once and integrate through a simple REST API.
            </p>
            <a href="{{ route('pricing') }}#whatsapp-messaging" class="mt-8 mkt-btn-secondary">Explore WhatsApp API</a>
        </div>
        <div class="mx-auto mt-10 max-w-xl rounded-2xl border border-slate-200 bg-slate-50 p-6 font-mono text-xs text-slate-700 mkt-reveal">
            <p class="text-slate-400">POST /api/v1/messages/send</p>
            <p class="mt-2">{ "mobile": "9876543210", "message": "Your report is ready." }</p>
        </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section id="how-it-works" class="mkt-section bg-slate-50">
    <div class="mkt-container">
        <div class="mx-auto max-w-2xl text-center mkt-reveal">
            <h2 class="mkt-headline">Start in minutes.</h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['01', 'Add your leads', 'Capture enquiries from ads, WhatsApp, website and referrals.'],
                ['02', 'Connect WhatsApp', 'Link your business number once from the dashboard.'],
                ['03', 'Schedule follow-ups', 'Set the next call, message or demo for every lead.'],
                ['04', 'Close more deals', 'Act on overdue and today\'s tasks from one workspace.'],
            ] as [$num, $title, $desc])
                <div class="mkt-card mkt-reveal text-center">
                    <span class="text-3xl font-extrabold text-brand-200">{{ $num }}</span>
                    <h3 class="mt-3 font-semibold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FEATURES GRID --}}
<section id="features" class="mkt-section bg-white">
    <div class="mkt-container">
        <div class="mx-auto max-w-2xl text-center mkt-reveal">
            <h2 class="mkt-headline">Everything in one place.</h2>
        </div>
        <div class="mkt-grid-features mt-12">
            @foreach([
                'Lead Management', 'Follow-up Automation', 'WhatsApp Inbox', 'Sales Pipeline',
                'Lead Sources', 'Activity Timeline', 'Notes', 'Reports',
                'Global Search', 'Mobile CRM', 'WhatsApp API', 'Message Logs', 'Credit Wallet',
            ] as $feature)
                <div class="mkt-feature-item mkt-reveal">
                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-[10px] text-brand-700">✓</span>
                    <span class="text-sm font-medium text-slate-800">{{ $feature }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="mkt-section bg-brand-600">
    <div class="mkt-container text-center mkt-reveal">
        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Stop losing leads to missed follow-ups.</h2>
        <p class="mx-auto mt-4 max-w-2xl text-brand-100">
            Capture every lead. Follow up on time. Keep WhatsApp conversations organized. Close more business.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">Start with ConnectPulse</a>
            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 px-8 py-3 text-sm font-semibold text-white hover:bg-white/10">Talk to Sales</a>
        </div>
    </div>
</section>
@endsection
