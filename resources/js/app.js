import Chart from 'chart.js/auto';
window.Chart = Chart;

/**
 * Openbox shared vanilla JS utilities.
 * No Alpine, no Livewire — every interactive piece here is plain DOM.
 */

function bindDrawer(openId, closeId, panelId, overlayId) {
    const open = document.getElementById(openId);
    const close = document.getElementById(closeId);
    const panel = document.getElementById(panelId);
    const overlay = document.getElementById(overlayId);

    if (!panel || !overlay) return;

    const show = () => {
        panel.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    };

    const hide = () => {
        panel.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    };

    open?.addEventListener('click', show);
    close?.addEventListener('click', hide);
    overlay.addEventListener('click', hide);
}

// Public site mobile nav drawer
bindDrawer('drawer-open', 'drawer-close', 'mobile-drawer', 'drawer-overlay');

// Dashboard sidebar drawer (admin/business/saler/verifier)
bindDrawer('sidebar-open', 'sidebar-close', 'sidebar', 'sidebar-overlay');

// Tabs — any element with [data-tab] toggles the matching [data-tab-content]
document.querySelectorAll('[data-tab]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        const group = btn.closest('[data-tab-group]') ?? document;

        group.querySelectorAll('[data-tab-content]').forEach((c) => c.classList.add('hidden'));
        group.querySelectorAll('[data-tab]').forEach((b) => {
            b.classList.remove('border-brand-500', 'text-brand-600');
            b.classList.add('border-transparent', 'text-gray-500');
        });

        group.querySelector(`[data-tab-content="${target}"]`)?.classList.remove('hidden');
        btn.classList.add('border-brand-500', 'text-brand-600');
        btn.classList.remove('border-transparent', 'text-gray-500');
    });
});

// FAQ accordion
document.querySelectorAll('.faq-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const answer = btn.nextElementSibling;
        const icon = btn.querySelector('.faq-icon');
        answer.classList.toggle('hidden');
        if (icon) icon.textContent = answer.classList.contains('hidden') ? '+' : '−';
    });
});

// Generic modal open/close — [data-modal-open="modal-id"] and [data-modal-close]
document.querySelectorAll('[data-modal-open]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.modalOpen)?.classList.remove('hidden');
    });
});
document.querySelectorAll('[data-modal-close]').forEach((btn) => {
    btn.addEventListener('click', () => {
        btn.closest('[data-modal]')?.classList.add('hidden');
    });
});

// Notification bell + user avatar dropdowns (dashboard topbar)
document.getElementById('notif-btn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('notif-dropdown')?.classList.toggle('hidden');
});
document.getElementById('user-btn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('user-dropdown')?.classList.toggle('hidden');
});
document.addEventListener('click', (e) => {
    if (!e.target.closest('#notif-btn') && !e.target.closest('#notif-dropdown')) {
        document.getElementById('notif-dropdown')?.classList.add('hidden');
    }
    if (!e.target.closest('#user-btn') && !e.target.closest('#user-dropdown')) {
        document.getElementById('user-dropdown')?.classList.add('hidden');
    }
});

// Confirm before destructive actions — <a data-confirm="Delete this product?">
document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => {
        if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
});

// Quick-approve KYC verification — top-middle confirm card, AJAX submit, top-middle result card
(function () {
    const confirmCard = document.getElementById('kyc-approve-confirm-card');
    const resultCard = document.getElementById('kyc-approve-result-card');
    if (!confirmCard || !resultCard) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const badgeColors = {
        green: 'bg-green-50 text-green-600',
        blue: 'bg-blue-50 text-blue-600',
        amber: 'bg-brand-50 text-brand-600',
        red: 'bg-red-50 text-red-500',
        gray: 'bg-gray-100 text-gray-500',
    };
    let resultTimeout = null;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function hideConfirmCard() {
        confirmCard.classList.add('hidden');
        confirmCard.innerHTML = '';
    }

    function showResultCard(message, tone) {
        clearTimeout(resultTimeout);
        const styles = tone === 'error'
            ? 'bg-red-50 border-red-200 text-red-800 shadow-xl'
            : 'bg-emerald-50 border-emerald-200 text-emerald-800 shadow-xl';

        const icon = tone === 'error'
            ? `<svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
            : `<svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;

        resultCard.innerHTML = `
            <div class="flex items-start gap-3 border rounded-xl p-4 text-sm font-semibold ${styles}">
                ${icon}
                <div class="flex-1 leading-snug">${escapeHtml(message)}</div>
                <button type="button" data-result-close class="text-current opacity-50 hover:opacity-100 transition p-0.5 rounded cursor-pointer leading-none">&times;</button>
            </div>
        `;
        resultCard.classList.remove('hidden');
        resultTimeout = setTimeout(() => resultCard.classList.add('hidden'), 4000);
    }

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-result-close]')) {
            clearTimeout(resultTimeout);
            resultCard.classList.add('hidden');
            return;
        }

        const btn = e.target.closest('[data-approve-btn]');
        if (!btn) return;

        const { approveUrl, approveName, approveRow } = btn.dataset;

        confirmCard.innerHTML = `
            <div class="border border-gray-200 rounded-md px-4 py-3.5 text-sm bg-white shadow-lg">
                <p class="text-gray-800">Approve KYC verification for <span class="font-semibold">${escapeHtml(approveName)}</span>?</p>
                <div class="flex items-center justify-end gap-2 mt-3">
                    <button type="button" data-approve-cancel class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-100 transition cursor-pointer">Cancel</button>
                    <button type="button" data-approve-confirm class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition cursor-pointer">Confirm Approve</button>
                </div>
            </div>
        `;
        confirmCard.classList.remove('hidden');

        confirmCard.querySelector('[data-approve-cancel]').addEventListener('click', hideConfirmCard);

        confirmCard.querySelector('[data-approve-confirm]').addEventListener('click', async (evt) => {
            const confirmBtn = evt.currentTarget;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Approving…';

            try {
                const response = await fetch(approveUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                hideConfirmCard();
                showResultCard(data.message, 'success');

                const statusCell = document.getElementById(`verification-status-${approveRow}`);
                if (statusCell) {
                    const colorClass = badgeColors[data.badge_color] || badgeColors.gray;
                    statusCell.innerHTML = `<span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded ${colorClass}">${escapeHtml(data.status_label)}</span>`;
                }

                btn.remove();
            } catch (err) {
                hideConfirmCard();
                showResultCard(err.message || 'Failed to approve verification.', 'error');
            }
        });
    });
})();

// Password show/hide toggle — <button data-password-toggle="field-id">
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;
    const input = document.getElementById(btn.dataset.passwordToggle);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    if (eyeOpen && eyeClosed) {
        eyeOpen.classList.toggle('hidden', isPassword);
        eyeClosed.classList.toggle('hidden', !isPassword);
    }
});

// Password strength bar — <input data-strength-for> + <div id="strength-bar">
document.querySelectorAll('[data-strength-for]').forEach((input) => {
    input.addEventListener('input', function () {
        const bar = document.getElementById(this.dataset.strengthFor);
        if (!bar) return;

        let score = 0;
        if (this.value.length >= 8) score++;
        if (/[A-Z]/.test(this.value)) score++;
        if (/[0-9]/.test(this.value)) score++;
        if (/[^A-Za-z0-9]/.test(this.value)) score++;

        const colors = ['bg-red-500', 'bg-orange-400', 'bg-yellow-400', 'bg-green-400', 'bg-green-600'];
        const widths = ['w-1/4', 'w-2/4', 'w-3/4', 'w-full'];

        bar.className = this.value.length
            ? `h-1 rounded-md transition-all ${colors[score]} ${widths[Math.max(0, score - 1)]}`
            : 'h-1 rounded-md transition-all';
    });
});

// Cart / quantity steppers — [data-qty-btn][data-dir=up|down] next to .qty-input
document.querySelectorAll('.qty-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('.qty-input');
        if (!input) return;
        let val = parseInt(input.value, 10) || 1;
        if (btn.dataset.dir === 'up') val = Math.min(val + 1, 99);
        if (btn.dataset.dir === 'down') val = Math.max(val - 1, 1);
        input.value = val;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

// Product gallery thumbnail swap
document.querySelectorAll('.thumb-img').forEach((thumb) => {
    thumb.addEventListener('click', () => {
        const main = document.getElementById('main-img');
        if (main) main.src = thumb.src;
        document.querySelectorAll('.thumb-img').forEach((t) => t.classList.remove('ring-2', 'ring-brand-500'));
        thumb.classList.add('ring-2', 'ring-brand-500');
    });
});

// Export a table's rows to CSV — used by <x-export-btn>
window.exportTable = function exportTable(tableId, filename = 'export') {
    const rows = Array.from(document.querySelectorAll(`#${tableId} tr`));
    const csv = rows
        .map((r) =>
            Array.from(r.querySelectorAll('th,td'))
                .map((c) => `"${c.innerText.replace(/"/g, '""').trim()}"`)
                .join(','),
        )
        .join('\n');

    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(new Blob([csv], { type: 'text/csv' })),
        download: `${filename}.csv`,
    });
    a.click();
    URL.revokeObjectURL(a.href);
};

// Bulk-select checkboxes — #select-all toggles .row-check, reveals #bulk-bar
document.getElementById('select-all')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach((c) => (c.checked = this.checked));
    toggleBulkBar();
});
document.querySelectorAll('.row-check').forEach((c) => c.addEventListener('change', toggleBulkBar));

function toggleBulkBar() {
    const any = Array.from(document.querySelectorAll('.row-check')).some((c) => c.checked);
    document.getElementById('bulk-bar')?.classList.toggle('hidden', !any);
}

// Real-Time Notification Auto-Poller (Option 1)
function initNotificationPoller() {
    const container = document.getElementById('topbar-notif-container');
    if (!container) return;

    const feedUrl = container.dataset.feedUrl;
    const csrfToken = container.dataset.csrf;
    if (!feedUrl) return;

    let previousCount = null;

    async function pollNotifications() {
        try {
            const response = await fetch(feedUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store'
            });

            if (!response.ok) return;
            const data = await response.json();

            const badgeIndicator = document.getElementById('notif-badge-indicator');
            const countBadge = document.getElementById('notif-count-badge');
            const listContainer = document.getElementById('notif-items-list');

            const count = data.unread_count ?? 0;

            // Update badge indicator on the bell
            if (badgeIndicator) {
                if (count > 0) {
                    badgeIndicator.classList.remove('hidden');
                } else {
                    badgeIndicator.classList.add('hidden');
                }
            }

            // Update count label in the dropdown header
            if (countBadge) {
                if (count > 0) {
                    countBadge.textContent = `${count} new`;
                    countBadge.classList.remove('hidden');
                } else {
                    countBadge.classList.add('hidden');
                }
            }

            // Update the dropdown list
            if (listContainer) {
                if (!data.notifications || data.notifications.length === 0) {
                    listContainer.innerHTML = '<p class="px-4 py-6 text-center text-gray-400 text-xs">You\'re all caught up.</p>';
                } else {
                    listContainer.innerHTML = data.notifications.map((item) => `
                        <form method="POST" action="${item.read_url}" class="block">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition-colors cursor-pointer">
                                <p class="text-gray-800 font-medium text-xs">${item.title}</p>
                                <p class="text-gray-500 text-xs mt-0.5 line-clamp-2">${item.body}</p>
                                <p class="text-gray-400 text-[10px] mt-1">${item.time_ago}</p>
                            </button>
                        </form>
                    `).join('');
                }
            }

            // Animate bell on new incoming notifications
            if (previousCount !== null && count > previousCount) {
                const notifBtn = document.getElementById('notif-btn');
                if (notifBtn) {
                    notifBtn.classList.add('animate-bounce');
                    setTimeout(() => notifBtn.classList.remove('animate-bounce'), 1200);
                }
            }

            previousCount = count;
        } catch (e) {
            // Silently fail if network interrupted
        }
    }

    // Poll every 15 seconds
    setInterval(pollNotifications, 15000);

    // Also poll immediately when the tab becomes active again
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            pollNotifications();
        }
    });
}

// Start poller once DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotificationPoller);
} else {
    initNotificationPoller();
}

// Image Deletion Helpers
window.deleteExistingImage = function (btn, deleteUrl) {
    if (confirm('Are you sure you want to delete this image?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrl;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
};

window.markImageForDeletion = function (btn) {
    const wrapper = btn.closest('.js-file-upload');
    if (wrapper) {
        const removeFlag = wrapper.querySelector('.js-remove-flag');
        if (removeFlag) removeFlag.value = '1';
        const existingPreview = wrapper.querySelector('.js-existing-preview');
        if (existingPreview) existingPreview.classList.add('hidden');
        const deletedNotice = wrapper.querySelector('.js-existing-deleted-notice');
        if (deletedNotice) {
            deletedNotice.classList.remove('hidden');
            deletedNotice.classList.add('flex');
        }
        const fileInput = wrapper.querySelector('.js-file-input');
        if (fileInput) fileInput.value = '';
    }
};

window.undoImageDeletion = function (btn) {
    const wrapper = btn.closest('.js-file-upload');
    if (wrapper) {
        const removeFlag = wrapper.querySelector('.js-remove-flag');
        if (removeFlag) removeFlag.value = '0';
        const existingPreview = wrapper.querySelector('.js-existing-preview');
        if (existingPreview) existingPreview.classList.remove('hidden');
        const deletedNotice = wrapper.querySelector('.js-existing-deleted-notice');
        if (deletedNotice) {
            deletedNotice.classList.add('hidden');
            deletedNotice.classList.remove('flex');
        }
    }
};

// File Upload Live Preview & Delete Handler
window.initFileUploads = function initFileUploads() {
    document.querySelectorAll('.js-file-upload').forEach((wrapper) => {
        if (wrapper.dataset.initialized) return;
        wrapper.dataset.initialized = 'true';

        const fileInput = wrapper.querySelector('.js-file-input');
        const dropzone = wrapper.querySelector('.js-dropzone');
        const newPreviewContainer = wrapper.querySelector('.js-new-preview-container');
        const existingPreview = wrapper.querySelector('.js-existing-preview');
        const deleteExistingBtn = wrapper.querySelector('.js-delete-existing-btn');
        const existingDeletedNotice = wrapper.querySelector('.js-existing-deleted-notice');
        const undoDeleteExistingBtn = wrapper.querySelector('.js-undo-delete-existing');
        const removeFlag = wrapper.querySelector('.js-remove-flag');

        if (!fileInput) return;

        let activeObjectUrls = [];

        function clearNewPreviews() {
            activeObjectUrls.forEach((url) => URL.revokeObjectURL(url));
            activeObjectUrls = [];
            if (newPreviewContainer) {
                newPreviewContainer.innerHTML = '';
                newPreviewContainer.classList.add('hidden');
            }
        }

        function renderNewPreviews(files) {
            clearNewPreviews();
            if (!files || files.length === 0) return;

            if (newPreviewContainer) {
                newPreviewContainer.classList.remove('hidden');
            }

            Array.from(files).forEach((file) => {
                if (!file.type.startsWith('image/')) {
                    const card = document.createElement('div');
                    card.className = 'flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-gray-50';
                    card.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-md bg-gray-200 flex items-center justify-center text-gray-500 font-semibold text-xs shrink-0">
                                FILE
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-800 truncate">${file.name}</p>
                                <p class="text-[11px] text-gray-400">${(file.size / 1024).toFixed(1)} KB</p>
                            </div>
                        </div>
                        <button type="button" class="js-remove-file-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-md border border-rose-200/60 transition-colors" title="Remove file">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete
                        </button>
                    `;
                    card.querySelector('.js-remove-file-btn').addEventListener('click', () => {
                        fileInput.value = '';
                        clearNewPreviews();
                    });
                    newPreviewContainer.appendChild(card);
                    return;
                }

                const url = URL.createObjectURL(file);
                activeObjectUrls.push(url);

                const card = document.createElement('div');
                card.className = 'flex items-center justify-between p-3 border border-brand-200 rounded-lg bg-brand-50/20';
                card.innerHTML = `
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="${url}" alt="${file.name}" class="w-14 h-14 rounded-md object-contain border border-gray-200 bg-white p-1 shrink-0">
                        <div class="min-w-0">
                            <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold bg-brand-100 text-brand-700 rounded mb-0.5">New Image</span>
                            <p class="text-xs font-semibold text-gray-800 truncate">${file.name}</p>
                            <p class="text-[11px] text-gray-400">${(file.size / 1024).toFixed(1)} KB</p>
                        </div>
                    </div>
                    <button type="button" class="js-remove-file-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-md border border-rose-200/60 transition-colors" title="Delete selected image">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                `;

                card.querySelector('.js-remove-file-btn').addEventListener('click', () => {
                    fileInput.value = '';
                    clearNewPreviews();
                });

                newPreviewContainer.appendChild(card);
            });
        }

        fileInput.addEventListener('change', (e) => {
            renderNewPreviews(e.target.files);
            if (removeFlag) removeFlag.value = '0';
        });

        if (dropzone) {
            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-brand-500', 'bg-brand-50/30');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-brand-500', 'bg-brand-50/30');
                });
            });

            dropzone.addEventListener('drop', (e) => {
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    renderNewPreviews(fileInput.files);
                    if (removeFlag) removeFlag.value = '0';
                }
            });
        }

        deleteExistingBtn?.addEventListener('click', () => {
            if (removeFlag) removeFlag.value = '1';
            existingPreview?.classList.add('hidden');
            existingDeletedNotice?.classList.remove('hidden');
            existingDeletedNotice?.classList.add('flex');
            fileInput.value = '';
            clearNewPreviews();
        });

        undoDeleteExistingBtn?.addEventListener('click', () => {
            if (removeFlag) removeFlag.value = '0';
            existingPreview?.classList.remove('hidden');
            existingDeletedNotice?.classList.add('hidden');
            existingDeletedNotice?.classList.remove('flex');
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initFileUploads);
} else {
    window.initFileUploads();
}
