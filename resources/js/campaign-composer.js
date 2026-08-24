export function initCampaignComposer() {
    const form = document.getElementById('campaign-form');
    if (!form) return;

    const meta = JSON.parse(document.getElementById('campaign-audience-meta')?.textContent || '{}');
    const businessName = document.getElementById('campaign-business-name')?.textContent?.trim() || 'Your Business';
    const whatsappConnected = document.getElementById('campaign-whatsapp-connected')?.textContent === '1';

    const els = {
        name: form.querySelector('[name="name"]'),
        audienceType: form.querySelector('#audience-type'),
        messageBody: form.querySelector('#message-body'),
        mediaInput: form.querySelector('#media-input'),
        delayMin: form.querySelector('[name="delay_min_seconds"]'),
        delayMax: form.querySelector('[name="delay_max_seconds"]'),
        previewMessage: document.getElementById('preview-message'),
        previewImage: document.getElementById('preview-image'),
        previewImageWrap: document.getElementById('preview-image-wrap'),
        previewBusiness: document.getElementById('preview-business'),
        previewTime: document.getElementById('preview-time'),
        previewAvatar: document.getElementById('preview-avatar'),
        charCount: document.getElementById('char-count'),
        recipientCount: document.querySelectorAll('[data-recipient-count]'),
        creditEstimate: document.querySelectorAll('[data-credit-estimate]'),
        statusMessage: document.getElementById('status-message'),
        statusMedia: document.getElementById('status-media'),
        statusRecipients: document.getElementById('status-recipients'),
        validationBanner: document.getElementById('validation-banner'),
        validationText: document.getElementById('validation-text'),
        saveBtn: document.getElementById('save-campaign-btn'),
        stickySaveBtn: document.getElementById('sticky-save-btn'),
        mediaUploadArea: document.getElementById('media-upload-area'),
        mediaPreviewWrap: document.getElementById('media-preview-wrap'),
        mediaPreviewImg: document.getElementById('media-preview-img'),
        audienceSummary: document.getElementById('audience-summary'),
        audienceChipDisplay: document.getElementById('audience-chip-display'),
        whatsappWarning: document.getElementById('whatsapp-warning'),
        scheduleField: document.getElementById('schedule-field'),
    };

    const panels = {
        contact_list: document.getElementById('audience-list'),
        tags: document.getElementById('audience-tags'),
        manual: document.getElementById('audience-manual'),
        csv: document.getElementById('audience-csv'),
    };

    const audienceLabels = {
        all_contacts: 'All customers',
        contact_list: 'Customer list',
        tags: 'By tag',
        leads: 'All leads',
        manual: 'Pick customers',
        csv: 'Paste phone numbers',
    };

    let mediaObjectUrl = null;

    function updatePreviewTime() {
        if (!els.previewTime) return;
        const now = new Date();
        els.previewTime.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function renderPreviewMessage() {
        const raw = els.messageBody?.value || '';
        const rendered = raw.replace(/\{\{name\}\}/gi, 'Ravi') || 'Write your message…';
        if (els.previewMessage) {
            els.previewMessage.textContent = rendered;
            els.previewMessage.classList.toggle('text-white/40', !raw.trim());
        }
        if (els.charCount) {
            els.charCount.textContent = `${raw.length} / 4096`;
        }
        updateStatus();
    }

    function updatePreviewBusiness() {
        // Preview chat title is the business, not the campaign name
        if (els.previewBusiness) els.previewBusiness.textContent = businessName;
        if (els.previewAvatar) els.previewAvatar.textContent = businessName.charAt(0).toUpperCase();
    }

    function countCsvPhones(text) {
        if (!text?.trim()) return 0;
        return text.trim().split(/\r\n|\r|\n/).filter((l) => l.trim()).length;
    }

    function countRecipients() {
        const type = els.audienceType?.value;
        if (!type) return 0;

        switch (type) {
            case 'all_contacts':
                return meta.total_contacts || 0;
            case 'contact_list': {
                const listId = form.querySelector('[name="contact_list_id"]')?.value;
                return listId ? (meta.lists?.[listId] || 0) : 0;
            }
            case 'tags': {
                const checked = form.querySelectorAll('[name="tag_ids[]"]:checked');
                let total = 0;
                checked.forEach((cb) => { total += meta.tags?.[cb.value] || 0; });
                return total;
            }
            case 'leads':
                return meta.total_leads || 0;
            case 'manual': {
                const contacts = form.querySelectorAll('[name="contact_ids[]"]:checked').length;
                const leads = form.querySelectorAll('[name="lead_ids[]"]:checked').length;
                return contacts + leads;
            }
            case 'csv':
                return countCsvPhones(form.querySelector('[name="csv_phones"]')?.value);
            default:
                return 0;
        }
    }

    function getAudienceLabel() {
        const type = els.audienceType?.value;
        if (type === 'contact_list') {
            const sel = form.querySelector('[name="contact_list_id"]');
            const opt = sel?.selectedOptions?.[0];
            return opt?.textContent?.split(' (')[0] || audienceLabels[type];
        }
        if (type === 'tags') {
            const names = [...form.querySelectorAll('[name="tag_ids[]"]:checked')]
                .map((cb) => cb.closest('label')?.textContent?.trim())
                .filter(Boolean);
            return names.length ? names.join(', ') : audienceLabels[type];
        }
        return audienceLabels[type] || type;
    }

    function updateRecipientDisplay() {
        const count = countRecipients();
        els.recipientCount.forEach((el) => { el.textContent = count.toLocaleString(); });
        els.creditEstimate.forEach((el) => { el.textContent = count.toLocaleString(); });

        if (els.audienceSummary) {
            els.audienceSummary.textContent = count > 0
                ? `${count.toLocaleString()} customer${count === 1 ? '' : 's'} will receive this message`
                : 'Choose who should receive this message';
        }

        if (els.audienceChipDisplay) {
            els.audienceChipDisplay.textContent = getAudienceLabel();
        }

        updateStatus();
        updateValidation();
        return count;
    }

    function updateStatus() {
        const hasMessage = (els.messageBody?.value || '').trim().length > 0;
        const hasMedia = els.mediaInput?.files?.length > 0;
        const count = countRecipients();

        if (els.statusMessage) {
            els.statusMessage.innerHTML = hasMessage
                ? '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Message ready</span>'
                : '<span class="text-amber-600">Write your message</span>';
            els.statusMessage.className = `wa-composer-status-item ${hasMessage ? 'wa-composer-status-ok' : 'wa-composer-status-warn'}`;
        }

        if (els.statusMedia) {
            els.statusMedia.innerHTML = hasMedia
                ? '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Photo attached</span>'
                : '<span class="text-slate-400">No photo</span>';
            els.statusMedia.className = `wa-composer-status-item ${hasMedia ? 'wa-composer-status-ok' : ''}`;
        }

        if (els.statusRecipients) {
            els.statusRecipients.innerHTML = count > 0
                ? `<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>${count.toLocaleString()} recipient${count === 1 ? '' : 's'}</span>`
                : '<span class="text-amber-600">Choose audience</span>';
            els.statusRecipients.className = `wa-composer-status-item ${count > 0 ? 'wa-composer-status-ok' : 'wa-composer-status-warn'}`;
        }
    }

    function getValidationErrors() {
        const errors = [];
        if (!els.name?.value?.trim()) errors.push('Give your campaign a name.');
        if (!(els.messageBody?.value || '').trim()) errors.push('Write a message before saving.');
        if (countRecipients() <= 0) errors.push('Choose at least one customer.');
        const sendMode = form.querySelector('[name="send_mode"]:checked')?.value;
        if (sendMode === 'schedule') {
            const scheduled = form.querySelector('[name="scheduled_at"]')?.value;
            if (!scheduled) errors.push('Pick a date and time for the schedule.');
        }
        return errors;
    }

    function updateValidation() {
        const errors = getValidationErrors();
        const canSave = errors.length === 0;

        if (els.validationBanner) {
            els.validationBanner.classList.toggle('hidden', canSave);
            if (els.validationText) {
                els.validationText.textContent = errors[0] || '';
            }
        }

        if (els.saveBtn) els.saveBtn.disabled = !canSave;
        if (els.stickySaveBtn) els.stickySaveBtn.disabled = !canSave;

        if (els.whatsappWarning) {
            els.whatsappWarning.classList.toggle('hidden', whatsappConnected);
        }
    }

    function updateAudiencePanels() {
        Object.values(panels).forEach((p) => p?.classList.add('hidden'));
        const val = els.audienceType?.value;
        if (panels[val]) panels[val].classList.remove('hidden');
        updateRecipientDisplay();
    }

    function handleMediaChange() {
        const file = els.mediaInput?.files?.[0];
        if (mediaObjectUrl) {
            URL.revokeObjectURL(mediaObjectUrl);
            mediaObjectUrl = null;
        }

        if (file) {
            mediaObjectUrl = URL.createObjectURL(file);
            if (els.previewImage) {
                els.previewImage.src = mediaObjectUrl;
                els.previewImage.classList.remove('hidden');
            }
            if (els.previewImageWrap) els.previewImageWrap.classList.remove('hidden');
            if (els.mediaPreviewImg) els.mediaPreviewImg.src = mediaObjectUrl;
            if (els.mediaUploadArea) els.mediaUploadArea.classList.add('hidden');
            if (els.mediaPreviewWrap) els.mediaPreviewWrap.classList.remove('hidden');
        } else {
            clearMedia();
        }
        updateStatus();
    }

    function clearMedia() {
        if (els.mediaInput) els.mediaInput.value = '';
        if (els.previewImage) {
            els.previewImage.src = '';
            els.previewImage.classList.add('hidden');
        }
        if (els.previewImageWrap) els.previewImageWrap.classList.add('hidden');
        if (els.mediaPreviewImg) els.mediaPreviewImg.src = '';
        if (els.mediaUploadArea) els.mediaUploadArea.classList.remove('hidden');
        if (els.mediaPreviewWrap) els.mediaPreviewWrap.classList.add('hidden');
        updateStatus();
    }

    function updateScheduleVisibility() {
        const isSchedule = form.querySelector('[name="send_mode"]:checked')?.value === 'schedule';
        els.scheduleField?.classList.toggle('hidden', !isSchedule);
        updateValidation();
    }

    // Event listeners
    els.audienceType?.addEventListener('change', updateAudiencePanels);
    form.querySelector('[name="contact_list_id"]')?.addEventListener('change', updateRecipientDisplay);
    form.querySelectorAll('[name="tag_ids[]"]').forEach((cb) => cb.addEventListener('change', updateRecipientDisplay));
    form.querySelectorAll('[name="contact_ids[]"], [name="lead_ids[]"]').forEach((cb) => cb.addEventListener('change', updateRecipientDisplay));
    form.querySelector('[name="csv_phones"]')?.addEventListener('input', updateRecipientDisplay);

    els.messageBody?.addEventListener('input', renderPreviewMessage);
    els.name?.addEventListener('input', updateValidation);
    els.mediaInput?.addEventListener('change', handleMediaChange);

    document.getElementById('media-remove-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        clearMedia();
    });
    document.getElementById('media-replace-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        els.mediaInput?.click();
    });
    els.mediaUploadArea?.addEventListener('click', () => els.mediaInput?.click());
    els.mediaUploadArea?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            els.mediaInput?.click();
        }
    });

    document.getElementById('insert-name-btn')?.addEventListener('click', () => {
        const ta = els.messageBody;
        if (!ta) return;
        const start = ta.selectionStart;
        const end = ta.selectionEnd;
        const tag = '{{name}}';
        ta.value = ta.value.slice(0, start) + tag + ta.value.slice(end);
        ta.selectionStart = ta.selectionEnd = start + tag.length;
        ta.focus();
        renderPreviewMessage();
    });

    form.querySelectorAll('[name="send_mode"]').forEach((radio) => {
        radio.addEventListener('change', updateScheduleVisibility);
    });
    form.querySelector('[name="scheduled_at"]')?.addEventListener('change', updateValidation);

    form.addEventListener('submit', (e) => {
        const errors = getValidationErrors();
        if (errors.length) {
            e.preventDefault();
            updateValidation();
            els.validationBanner?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    initMobileSync(form, updateRecipientDisplay);

    updatePreviewTime();
    setInterval(updatePreviewTime, 60000);
    updatePreviewBusiness();
    renderPreviewMessage();
    updateAudiencePanels();
    updateScheduleVisibility();
    updateValidation();
}

function initMobileSync(form, updateRecipientDisplay) {
    const desktopType = form.querySelector('#audience-type');
    const mobileType = document.getElementById('audience-type-mobile');
    if (!desktopType || !mobileType) return;

    const mobilePanels = {
        contact_list: document.getElementById('audience-list-mobile'),
        tags: document.getElementById('audience-tags-mobile'),
        manual: document.getElementById('audience-manual-mobile'),
        csv: document.getElementById('audience-csv-mobile'),
    };

    function showMobilePanel(val) {
        Object.values(mobilePanels).forEach((p) => p?.classList.add('hidden'));
        if (mobilePanels[val]) mobilePanels[val].classList.remove('hidden');
    }

    function syncDesktopToMobile() {
        mobileType.value = desktopType.value;
        showMobilePanel(desktopType.value);

        const listDesktop = form.querySelector('[name="contact_list_id"]');
        const listMobile = document.getElementById('contact-list-mobile');
        if (listDesktop && listMobile) listMobile.value = listDesktop.value;

        form.querySelectorAll('[name="tag_ids[]"]').forEach((cb, i) => {
            const mobileCb = document.querySelectorAll('.tag-mobile-cb')[i];
            if (mobileCb) mobileCb.checked = cb.checked;
        });

        form.querySelectorAll('[name="contact_ids[]"]').forEach((cb, i) => {
            const mobileCb = document.querySelectorAll('.contact-mobile-cb')[i];
            if (mobileCb) mobileCb.checked = cb.checked;
        });

        form.querySelectorAll('[name="lead_ids[]"]').forEach((cb, i) => {
            const mobileCb = document.querySelectorAll('.lead-mobile-cb')[i];
            if (mobileCb) mobileCb.checked = cb.checked;
        });

        const csvDesktop = form.querySelector('[name="csv_phones"]');
        const csvMobile = document.getElementById('csv-mobile');
        if (csvDesktop && csvMobile) csvMobile.value = csvDesktop.value;

        const delayMinDesktop = form.querySelector('[name="delay_min_seconds"]');
        const delayMaxDesktop = form.querySelector('[name="delay_max_seconds"]');
        const delayMinMobile = document.getElementById('delay-min-mobile');
        const delayMaxMobile = document.getElementById('delay-max-mobile');
        if (delayMinDesktop && delayMinMobile) delayMinMobile.value = delayMinDesktop.value;
        if (delayMaxDesktop && delayMaxMobile) delayMaxMobile.value = delayMaxDesktop.value;
    }

    function syncMobileToDesktop() {
        desktopType.value = mobileType.value;
        showMobilePanel(mobileType.value);

        const listDesktop = form.querySelector('[name="contact_list_id"]');
        const listMobile = document.getElementById('contact-list-mobile');
        if (listDesktop && listMobile) listDesktop.value = listMobile.value;

        document.querySelectorAll('.tag-mobile-cb').forEach((mobileCb, i) => {
            const desktopCb = form.querySelectorAll('[name="tag_ids[]"]')[i];
            if (desktopCb) desktopCb.checked = mobileCb.checked;
        });

        document.querySelectorAll('.contact-mobile-cb').forEach((mobileCb, i) => {
            const desktopCb = form.querySelectorAll('[name="contact_ids[]"]')[i];
            if (desktopCb) desktopCb.checked = mobileCb.checked;
        });

        document.querySelectorAll('.lead-mobile-cb').forEach((mobileCb, i) => {
            const desktopCb = form.querySelectorAll('[name="lead_ids[]"]')[i];
            if (desktopCb) desktopCb.checked = mobileCb.checked;
        });

        const csvDesktop = form.querySelector('[name="csv_phones"]');
        const csvMobile = document.getElementById('csv-mobile');
        if (csvDesktop && csvMobile) csvDesktop.value = csvMobile.value;

        const delayMinDesktop = form.querySelector('[name="delay_min_seconds"]');
        const delayMaxDesktop = form.querySelector('[name="delay_max_seconds"]');
        const delayMinMobile = document.getElementById('delay-min-mobile');
        const delayMaxMobile = document.getElementById('delay-max-mobile');
        if (delayMinDesktop && delayMinMobile) delayMinDesktop.value = delayMinMobile.value;
        if (delayMaxDesktop && delayMaxMobile) delayMaxDesktop.value = delayMaxMobile.value;

        desktopType.dispatchEvent(new Event('change'));
        updateRecipientDisplay();
    }

    mobileType.addEventListener('change', () => {
        showMobilePanel(mobileType.value);
        syncMobileToDesktop();
    });

    document.querySelectorAll('#audience-sheet [data-sheet-close]').forEach((btn) => {
        btn.addEventListener('click', syncMobileToDesktop);
    });

    document.querySelectorAll('#sending-sheet [data-sheet-close]').forEach((btn) => {
        btn.addEventListener('click', syncMobileToDesktop);
    });

    document.querySelectorAll('[data-sheet-open="audience-sheet"]').forEach((btn) => {
        btn.addEventListener('click', syncDesktopToMobile);
    });

    document.querySelectorAll('[data-sheet-open="sending-sheet"]').forEach((btn) => {
        btn.addEventListener('click', syncDesktopToMobile);
    });

    document.getElementById('contact-list-mobile')?.addEventListener('change', syncMobileToDesktop);
    document.querySelectorAll('.tag-mobile-cb, .contact-mobile-cb, .lead-mobile-cb').forEach((cb) => {
        cb.addEventListener('change', syncMobileToDesktop);
    });
    document.getElementById('csv-mobile')?.addEventListener('input', syncMobileToDesktop);
    document.getElementById('delay-min-mobile')?.addEventListener('change', syncMobileToDesktop);
    document.getElementById('delay-max-mobile')?.addEventListener('change', syncMobileToDesktop);
}
