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

// Password show/hide toggle — <button data-password-toggle="field-id">
document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.passwordToggle);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
    });
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
