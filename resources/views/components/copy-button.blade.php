@props(['value', 'label' => 'Copy'])

<button type="button"
        onclick="copyText(this, @js($value))"
        class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">
    {{ $label }}
</button>

@once
@push('scripts')
<script>
function copyText(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = original; }, 2000);
    });
}
</script>
@endpush
@endonce
