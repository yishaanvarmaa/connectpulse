@extends('layouts.marketing')

@section('title', 'ConnectPulse | CRM + WhatsApp for Businesses')
@section('meta_description', 'Manage leads, WhatsApp conversations, follow-ups and sales pipeline in one simple CRM with ConnectPulse. Never let a lead go cold.')

@section('content')
{{-- HERO --}}
<section class="mkt-hero">
    <div class="mkt-hero__glow"></div>
    <div class="mkt-wrap relative">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="mkt-reveal">
                <p class="mkt-eyebrow">The sales workspace for modern businesses</p>
                <h1 class="mkt-display mt-4 text-white">
                    Never let a lead<br>
                    <span class="mkt-gradient-text">go cold.</span>
                </h1>
                <p class="mt-5 max-w-lg text-base leading-relaxed text-slate-400 sm:text-lg">
                    ConnectPulse brings your leads, WhatsApp conversations, follow-ups and sales pipeline into one workspace.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="mkt-btn mkt-btn--primary">Start Free</a>
                    <a href="#how-it-works" class="mkt-btn mkt-btn--ghost">See How It Works</a>
                </div>
                <div class="mkt-tag-row mt-6">
                    <span>CRM</span><span>WhatsApp</span><span>Follow-ups</span><span>Pipeline</span>
                </div>
            </div>
            <div class="mkt-reveal min-w-0 lg:mr-0 xl:mr-0">
                <x-marketing.mockups.hero-dashboard />
            </div>
        </div>
    </div>
    <div class="mkt-trust-strip mt-10 lg:mt-14">
        <div class="mkt-wrap">
            <div class="mkt-trust-strip__track">
                @foreach(['Leads', 'Follow-ups', 'WhatsApp', 'Pipeline', 'Reports', 'Automation'] as $i => $item)
                    @if($i > 0)<span class="mkt-trust-strip__divider"></span>@endif
                    <span class="mkt-trust-strip__item">{{ $item }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- PROBLEM --}}
<section class="mkt-section mkt-section--navy">
    <div class="mkt-wrap">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="mkt-reveal">
                <h2 class="mkt-h2 text-white">Your leads are coming in.<br>The problem is what happens next.</h2>
                <p class="mt-4 mkt-body">Leads flow in from every channel — but without a system, they slip through the cracks.</p>
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach(['Instagram', 'Facebook', 'Website', 'WhatsApp', 'Phone', 'Referrals'] as $s)
                        <span class="mkt-source-tag">{{ $s }}</span>
                    @endforeach
                </div>
                <div class="mt-6 flex items-center gap-2 text-sm text-[#8b7cff]">
                    <span>↓</span> flowing into ConnectPulse
                </div>
            </div>
            <div class="mkt-reveal space-y-6">
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-red-400">Without ConnectPulse</p>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach(['Scattered WhatsApp chats', 'Sticky notes', 'Forgotten callbacks', 'Spreadsheet chaos', 'Missed follow-ups', 'Lost deals'] as $m)
                            <div class="mkt-messy-item">{{ $m }}</div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-emerald-400">With ConnectPulse</p>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach(['Leads organized', 'Follow-ups scheduled', 'WhatsApp in CRM', 'Pipeline visible', 'Next action clear', 'Deals won'] as $o)
                            <div class="mkt-organized-item">{{ $o }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- PRODUCT SHOWCASE --}}
<section id="product" class="mkt-section mkt-section--soft" data-nav-light>
    <div class="mkt-wrap">
        <div class="mkt-reveal max-w-3xl">
            <p class="mkt-eyebrow mkt-eyebrow--light">Product</p>
            <h2 class="mkt-h2 mt-3 text-slate-900">One place to run your sales.</h2>
        </div>
        <div class="relative mt-10 lg:mt-12 mkt-reveal mkt-showcase">
            <x-marketing.mockups.hero-dashboard />
            <div class="mkt-float-card--light absolute left-3 top-6 hidden rounded-2xl px-4 py-3 sm:block lg:left-4">
                <p class="text-[10px] font-bold uppercase text-red-600">Overdue</p>
                <p class="text-2xl font-bold text-slate-900">3</p>
            </div>
            <div class="mkt-float-card--light absolute right-3 top-1/4 hidden rounded-2xl px-4 py-3 sm:block">
                <p class="text-[10px] font-bold uppercase text-slate-500">Pipeline</p>
                <p class="text-2xl font-bold text-slate-900">₹1.24L</p>
            </div>
            <div class="mkt-float-card--light absolute bottom-10 right-3 hidden rounded-2xl px-4 py-3 sm:block">
                <p class="text-[10px] font-bold uppercase text-[#635bff]">New leads</p>
                <p class="text-2xl font-bold text-slate-900">5</p>
            </div>
            <div class="mkt-float-card--light absolute bottom-4 left-1/4 hidden rounded-2xl px-4 py-3 lg:block">
                <p class="text-[10px] font-bold uppercase text-amber-600">Demos today</p>
                <p class="text-2xl font-bold text-slate-900">2</p>
            </div>
        </div>
    </div>
</section>

{{-- BENTO FEATURES --}}
<section id="features" class="mkt-section mkt-section--soft pt-0" data-nav-light>
    <div class="mkt-wrap">
        <div class="mkt-bento">
            <div class="mkt-reveal lg:col-span-7">
                <div class="mkt-bento-card h-full">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#635bff]">Leads</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Capture and organize every opportunity.</h3>
                    <div class="mt-5 overflow-hidden rounded-xl border border-slate-200">
                        <div class="bg-slate-50 p-3">
                            @foreach([['Ravi Kumar', '₹24,999', 'Negotiation', 'Hot'], ['Priya Sharma', '₹18,500', 'Demo', 'Warm']] as [$n,$v,$st,$t])
                                <div class="mb-2 flex items-center gap-3 rounded-lg bg-white p-3 shadow-sm last:mb-0">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#635bff]/10 text-xs font-bold text-[#635bff]">{{ substr($n,0,1) }}</div>
                                    <div class="flex-1"><p class="text-sm font-semibold">{{ $n }}</p><p class="text-[10px] text-slate-500">{{ $st }}</p></div>
                                    <p class="text-sm font-bold">{{ $v }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="mkt-reveal lg:col-span-5">
                <div class="mkt-bento-card h-full">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#635bff]">Follow-ups</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900">Never forget the next action.</h3>
                    <div class="mt-4 overflow-hidden rounded-xl origin-top-left">
                        <x-marketing.mockups.followups />
                    </div>
                </div>
            </div>
            <div class="mkt-reveal lg:col-span-12">
                <div class="mkt-bento-card">
                    <div class="grid gap-6 lg:grid-cols-2 lg:items-center">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#635bff]">WhatsApp</p>
                            <h3 class="mt-2 text-2xl font-bold text-slate-900">Your CRM knows who they are. Your inbox knows what they said.</h3>
                            <p class="mt-3 mkt-body--light">Open a lead, see the conversation, reply, and schedule the next action — without switching tools.</p>
                        </div>
                        <x-marketing.mockups.inbox />
                    </div>
                </div>
            </div>
            <div class="mkt-reveal lg:col-span-12">
                <div class="mkt-bento-card mkt-bento-card--dark !bg-[#0d1020]">
                    <div class="grid gap-6 lg:grid-cols-2 lg:items-center">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#8b7cff]">Pipeline</p>
                            <h3 class="mt-2 text-2xl font-bold text-white">New → Contacted → Interested → Demo → Won</h3>
                            <p class="mt-3 text-slate-400">Know exactly how many opportunities you have and how much revenue is in your pipeline.</p>
                        </div>
                        <x-marketing.mockups.pipeline />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FOLLOW-UP DARK --}}
<section id="follow-ups" class="mkt-section mkt-section--gradient">
    <div class="mkt-wrap">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="mkt-reveal">
                <h2 class="mkt-h2 text-white">The difference between a lead and a customer?<br><span class="mkt-gradient-text">Follow-up.</span></h2>
                <div class="mt-10 grid grid-cols-3 gap-4">
                    @foreach([['OVERDUE', '3', 'text-red-400'], ['TODAY', '5', 'text-amber-400'], ['UPCOMING', '8', 'text-[#8b7cff]']] as [$l,$n,$c])
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center backdrop-blur">
                            <p class="text-[10px] font-bold tracking-wider {{ $c }}">{{ $l }}</p>
                            <p class="mt-1 text-4xl font-extrabold text-white">{{ $n }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach(['WhatsApp', 'Call', 'Complete', 'Reschedule'] as $a)
                        <span class="rounded-lg border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold text-white">{{ $a }}</span>
                    @endforeach
                </div>
            </div>
            <div class="mkt-reveal">
                <x-marketing.mockups.followups />
            </div>
        </div>
    </div>
</section>

{{-- WHATSAPP INBOX --}}
<section id="whatsapp" class="mkt-section mkt-section--soft" data-nav-light>
    <div class="mkt-wrap">
        <div class="mkt-reveal mx-auto max-w-3xl text-center">
            <h2 class="mkt-h2 text-slate-900">Your CRM knows who they are.<br>Your inbox knows what they said.</h2>
        </div>
        <div class="mt-10 mkt-reveal">
            <x-marketing.mockups.inbox />
        </div>
    </div>
</section>

{{-- PIPELINE --}}
<section id="pipeline" class="mkt-section mkt-section--dark">
    <div class="mkt-wrap">
        <div class="mkt-reveal mb-10 max-w-2xl">
            <h2 class="mkt-h2 text-white">See where every deal is going.</h2>
        </div>
        <div class="mkt-reveal overflow-hidden">
            <x-marketing.mockups.pipeline />
        </div>
    </div>
</section>

{{-- AD TO SALE --}}
<section class="mkt-section mkt-section--navy overflow-hidden">
    <div class="mkt-wrap">
        <div class="mkt-reveal text-center">
            <h2 class="mkt-h2 text-white">Your ads generate the lead.<br>ConnectPulse keeps it moving.</h2>
        </div>
        <div class="mt-12 mkt-reveal overflow-x-auto pb-4">
            <div class="flex min-w-max items-center justify-center gap-2 px-4 sm:gap-3">
                @foreach(['Instagram Ad', 'New Lead', 'ConnectPulse', 'WhatsApp', 'Follow-up', 'Demo', 'Negotiation', 'Won'] as $i => $step)
                    <span class="mkt-journey__node">{{ $step }}</span>
                    @if($i < 7)<span class="mkt-journey__arrow">→</span>@endif
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- MOBILE --}}
<section class="mkt-section mkt-section--dark">
    <div class="mkt-wrap">
        <div class="mkt-reveal text-center">
            <h2 class="mkt-h2 text-white">Your sales desk.<br>In your pocket.</h2>
            <p class="mx-auto mt-4 max-w-xl mkt-body">Check follow-ups, message leads, call customers, update deals — from your phone.</p>
        </div>
        <div class="relative mt-12 flex flex-wrap justify-center gap-6 overflow-hidden px-2 sm:gap-10 mkt-reveal">
            <div class="relative">
                <x-marketing.mockups.mobile-screen variant="home" />
                <span class="mkt-notif-float left-1/2 top-6 -translate-x-1/2 sm:left-auto sm:right-0 sm:top-8 sm:translate-x-0 sm:-mr-2 md:-mr-6">Follow-up due in 10 min</span>
            </div>
            <div class="relative hidden sm:block">
                <x-marketing.mockups.mobile-screen variant="lead" />
                <span class="mkt-notif-float left-0 top-1/2 -translate-y-1/2 -ml-2 md:-ml-6">₹24,999 opportunity</span>
            </div>
            <div class="relative hidden md:block">
                <x-marketing.mockups.mobile-screen variant="followup" />
                <span class="mkt-notif-float right-0 bottom-8 -mr-2 lg:-mr-4">New lead received</span>
            </div>
        </div>
    </div>
</section>

{{-- WHO IS IT FOR --}}
<section class="mkt-section mkt-section--gradient">
    <div class="mkt-wrap">
        <div class="mkt-reveal max-w-3xl">
            <h2 class="mkt-h2 text-white">Built for businesses that sell through conversations.</h2>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mkt-reveal">
            @foreach([
                ['Clinics', 'Track patient enquiries and appointment follow-ups.'],
                ['Diagnostics', 'Manage test enquiries from ads and referrals.'],
                ['Agencies', 'Organize leads from multiple client campaigns.'],
                ['SaaS', 'Track demos, trials and sales conversations.'],
                ['Service businesses', 'Keep every enquiry moving toward a sale.'],
                ['Small business', 'Replace spreadsheets and scattered WhatsApp chats.'],
            ] as [$title, $desc])
                <div class="mkt-industry-chip">
                    <h3 class="font-bold text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-slate-400">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- WHATSAPP API --}}
<section id="whatsapp-api" class="mkt-section mkt-section--dark">
    <div class="mkt-wrap">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="mkt-reveal">
                <p class="mkt-eyebrow">Developers</p>
                <h2 class="mt-3 mkt-h2 text-white">And yes,<br>there's an API too.</h2>
                <p class="mt-4 mkt-body">Connect WhatsApp to your own applications. Send report notifications, appointment reminders, payment receipts and transactional messages through a simple REST API.</p>
                <div class="mt-8 flex flex-col flex-wrap items-stretch gap-2 text-sm sm:flex-row sm:items-center sm:gap-3">
                    <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-center font-semibold text-white">WhatsApp</span>
                    <span class="hidden text-center text-[#8b7cff] sm:inline">→</span>
                    <span class="rounded-lg border border-[#635bff]/30 bg-[#635bff]/10 px-3 py-2 text-center font-semibold text-[#8b7cff]">ConnectPulse API</span>
                    <span class="hidden text-center text-[#8b7cff] sm:inline">→</span>
                    <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-center font-semibold text-white">Your Application</span>
                </div>
                <a href="{{ route('pricing') }}#whatsapp-messaging" class="mkt-btn mkt-btn--ghost mt-8">Explore WhatsApp API</a>
            </div>
            <div class="mkt-reveal">
                <div class="mkt-code-panel">
                    <div class="mkt-code-panel__header">
                        <span class="mkt-browser__dot bg-red-500/80"></span>
                        <span class="mkt-browser__dot bg-amber-500/80"></span>
                        <span class="mkt-browser__dot bg-emerald-500/80"></span>
                        <span class="ml-2">POST /api/v1/messages/send</span>
                    </div>
                    <div class="mkt-code-panel__body">
                        <span class="kw">curl</span> -X POST {{ url('/api/v1/messages/send') }} \<br>
                        &nbsp;&nbsp;-H <span class="str">"X-API-Key: YOUR_KEY"</span> \<br>
                        &nbsp;&nbsp;-H <span class="str">"Content-Type: application/json"</span> \<br>
                        &nbsp;&nbsp;-d <span class="str">'{</span><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="key">"mobile"</span>: <span class="str">"9876543210"</span>,<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="key">"message"</span>: <span class="str">"Your report is ready."</span><br>
                        &nbsp;&nbsp;<span class="str">}'</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section id="how-it-works" class="mkt-section mkt-section--soft" data-nav-light>
    <div class="mkt-wrap">
        <div class="mkt-reveal text-center">
            <h2 class="mkt-h2 text-slate-900">Start in minutes.</h2>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mkt-reveal">
            @foreach([
                ['01', 'Add your leads', 'Capture enquiries from ads, WhatsApp, website and referrals.'],
                ['02', 'Connect WhatsApp', 'Link your business number once from the dashboard.'],
                ['03', 'Schedule follow-ups', 'Set the next call, message or demo for every lead.'],
                ['04', 'Close more deals', 'Act on overdue and today\'s tasks from one workspace.'],
            ] as [$n,$t,$d])
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="text-3xl font-extrabold text-[#635bff]/30">{{ $n }}</span>
                    <h3 class="mt-3 font-bold text-slate-900">{{ $t }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="mkt-cta-section">
    <div class="mkt-cta-glow"></div>
    <div class="mkt-wrap relative text-center mkt-reveal">
        <h2 class="mkt-h2 text-white">Stop losing leads<br>to missed follow-ups.</h2>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-slate-400">
            Capture every lead. Follow up on time. Keep WhatsApp conversations organized. Close more business.
        </p>
        <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="mkt-btn mkt-btn--primary !px-10 !py-4 !text-base">Start with ConnectPulse</a>
            <a href="{{ route('contact') }}" class="mkt-btn mkt-btn--ghost !px-10 !py-4">Talk to Sales</a>
        </div>
    </div>
</section>
@endsection
