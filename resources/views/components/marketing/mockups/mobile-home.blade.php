<x-marketing.phone-frame>
    <div class="p-3">
        <p class="text-[10px] font-semibold text-slate-400">NEXT ACTION</p>
        <div class="mt-2 rounded-xl border border-red-200 bg-red-50 p-2.5">
            <p class="text-xs font-semibold text-slate-900">Ravi Kumar</p>
            <p class="text-[10px] text-red-600">Follow-up overdue</p>
        </div>
        <div class="mt-3 grid grid-cols-3 gap-1">
            @foreach([['3', 'Overdue'], ['5', 'Today'], ['2', 'New']] as [$n, $l])
                <div class="rounded-lg bg-white p-1.5 text-center shadow-sm">
                    <p class="text-sm font-bold text-slate-900">{{ $n }}</p>
                    <p class="text-[8px] text-slate-500">{{ $l }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex gap-1">
            <div class="flex-1 rounded-lg bg-emerald-600 py-2 text-center text-[9px] font-medium text-white">WhatsApp</div>
            <div class="flex-1 rounded-lg border border-slate-200 bg-white py-2 text-center text-[9px] font-medium">Call</div>
            <div class="flex-1 rounded-lg bg-brand-600 py-2 text-center text-[9px] font-medium text-white">Log</div>
        </div>
    </div>
</x-marketing.phone-frame>
