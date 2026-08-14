@props(['variant' => 'home'])

<div class="mkt-phone">
    <div class="mkt-phone__notch"></div>
    <div class="mkt-phone__screen p-3">
        @if($variant === 'lead')
            <p class="text-[10px] font-bold text-slate-400">LEAD</p>
            <p class="text-base font-bold text-slate-900">Ravi Kumar</p>
            <p class="text-xs text-slate-500">+91 98765 43210</p>
            <div class="mt-2 flex gap-1">
                <span class="rounded-full bg-[#635bff]/10 px-2 py-0.5 text-[9px] font-semibold text-[#635bff]">Negotiation</span>
                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[9px] font-semibold text-red-600">Hot</span>
            </div>
            <p class="mt-3 text-xl font-bold text-slate-900">₹24,999</p>
            <div class="mt-4 flex gap-1">
                <div class="flex-1 rounded-lg bg-emerald-600 py-2 text-center text-[9px] font-semibold text-white">WhatsApp</div>
                <div class="flex-1 rounded-lg border border-slate-200 py-2 text-center text-[9px] font-semibold">Call</div>
            </div>
        @elseif($variant === 'followup')
            <p class="text-[10px] font-bold text-red-500">OVERDUE</p>
            <p class="text-sm font-bold text-slate-900">Call Ravi Kumar</p>
            <p class="text-[10px] text-slate-500">10:30 AM · Diagnostic Package</p>
            <div class="mt-4 space-y-2">
                <div class="rounded-lg bg-[#635bff] py-2 text-center text-[10px] font-semibold text-white">Complete follow-up</div>
                <div class="rounded-lg border border-slate-200 py-2 text-center text-[10px] font-semibold text-slate-700">Reschedule</div>
            </div>
        @else
            <p class="text-[10px] font-bold text-slate-400">NEXT ACTION</p>
            <div class="mt-2 rounded-xl border border-red-200 bg-red-50 p-2.5">
                <p class="text-xs font-bold text-slate-900">Ravi Kumar</p>
                <p class="text-[10px] text-red-600">Follow-up overdue</p>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-1">
                @foreach([['3','Overdue'],['5','Today'],['2','New']] as [$n,$l])
                    <div class="rounded-lg bg-white p-1.5 text-center shadow-sm">
                        <p class="text-sm font-bold text-slate-900">{{ $n }}</p>
                        <p class="text-[8px] text-slate-500">{{ $l }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
