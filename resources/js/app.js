import { initCampaignComposer } from './campaign-composer.js';

document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initCopyButtons();
    initSlideOvers();
    initBottomSheets();
    initMobileSidebar();
    initWhatsAppComposer();
    initQuickDates();
    initCommandPalette();
    initCampaignComposer();
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
    toast.className = `pointer-events-auto flex items-center gap-2 rounded-lg border px-4 py-3 text-sm shadow-lg ${colors[type] || colors.info}`;
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
            try {
                await navigator.clipboard.writeText(btn.dataset.copy);
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
        trigger.addEventListener('click', () => closePanel(trigger.closest('[data-slideover]')));
    });
}

function initBottomSheets() {
    document.querySelectorAll('[data-sheet-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const id = trigger.dataset.sheetOpen;
            const sheet = document.getElementById(id);
            if (sheet) {
                sheet.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        });
    });

    document.querySelectorAll('[data-sheet-close]').forEach((trigger) => {
        trigger.addEventListener('click', () => closePanel(trigger.closest('[data-bottom-sheet]')));
    });
}

function closePanel(panel) {
    panel?.classList.add('hidden');
    if (!document.querySelector('[data-bottom-sheet]:not(.hidden), [data-slideover]:not(.hidden)')) {
        document.body.classList.remove('overflow-hidden');
    }
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

function initWhatsAppComposer() {
    const sheet = document.getElementById('whatsapp-composer-sheet');
    const form = document.getElementById('whatsapp-composer-form');
    const messageInput = document.getElementById('whatsapp-composer-message');
    const leadLabel = document.getElementById('whatsapp-composer-lead');

    document.querySelectorAll('[data-whatsapp-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!form || !sheet) return;
            form.action = btn.dataset.whatsappUrl || '#';
            if (leadLabel) leadLabel.textContent = btn.dataset.leadName || 'Lead';
            if (messageInput) messageInput.value = '';
            sheet.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            messageInput?.focus();
        });
    });

    document.querySelectorAll('[data-quick-reply]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (messageInput) messageInput.value = btn.dataset.quickReply || '';
            messageInput?.focus();
        });
    });
}

function initQuickDates() {
    document.querySelectorAll('[data-next-followup]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const input = form?.querySelector('input[name="next_scheduled_at"], input[name="scheduled_at"]');
            if (!input) return;

            const preset = btn.dataset.nextFollowup;
            if (preset === 'none') {
                input.value = '';
                return;
            }

            const d = new Date();
            if (preset === 'tomorrow') d.setDate(d.getDate() + 1);
            else if (preset === '3days') d.setDate(d.getDate() + 3);
            else if (preset === 'week') d.setDate(d.getDate() + 7);
            d.setHours(10, 0, 0, 0);

            const pad = (n) => String(n).padStart(2, '0');
            input.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        });
    });
}

function initCommandPalette() {
    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

    document.addEventListener('keydown', (e) => {
        if ((isMac ? e.metaKey : e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            window.location.href = '/search';
        }
    });

    document.querySelectorAll('[data-command-trigger]').forEach((el) => {
        el.addEventListener('focus', () => el.select?.());
    });
}

window.ConnectPulse = { showToast };
