document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initCopyButtons();
    initSlideOvers();
    initMobileSidebar();
});

function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((el) => {
        showToast(el.dataset.toast, el.dataset.toastType || 'success');
        el.remove();
    });
}

export function showToast(message, type = 'success') {
    const container = document.getElementById('cp-toast-container') || createToastContainer();
    const toast = document.createElement('div');
    const colors = {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
        error: 'border-red-200 bg-red-50 text-red-800',
        info: 'border-slate-200 bg-white text-slate-800',
    };
    toast.className = `pointer-events-auto flex items-center gap-2 rounded-lg border px-4 py-3 text-sm shadow-lg ${colors[type] || colors.info} animate-in fade-in slide-in-from-top-2`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'cp-toast-container';
    container.className = 'fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none max-w-sm';
    document.body.appendChild(container);
    return container;
}

function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
                const original = btn.innerHTML;
                btn.innerHTML = '<span class="text-emerald-600">Copied!</span>';
                setTimeout(() => { btn.innerHTML = original; }, 1500);
            } catch {
                showToast('Could not copy to clipboard', 'error');
            }
        });
    });
}

function initSlideOvers() {
    document.querySelectorAll('[data-slideover-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const id = trigger.dataset.slideoverOpen;
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    });

    document.querySelectorAll('[data-slideover-close]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const panel = trigger.closest('[data-slideover]');
            panel?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });
    });
}

function initMobileSidebar() {
    const openBtn = document.getElementById('mobile-sidebar-open');
    const closeBtn = document.getElementById('mobile-sidebar-close');
    const sidebar = document.getElementById('mobile-sidebar');
    const backdrop = document.getElementById('mobile-sidebar-backdrop');

    openBtn?.addEventListener('click', () => {
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
    });

    const close = () => {
        sidebar?.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
    };

    closeBtn?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);
}

window.ConnectPulse = { showToast };
