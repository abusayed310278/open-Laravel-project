<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-gray-950 tracking-tight">Identity Verification & Inspection Management</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage user identity KYC applications, document requirements, inspection hubs, and verifier staff.</p>
    </div>

    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
        <a
            href="{{ route('admin.verifications.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ request()->routeIs('admin.verifications.*') ? 'bg-gray-900 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}"
        >
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>User KYC Applications</span>
        </a>

        <a
            href="{{ route('admin.verification-requirements.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ request()->routeIs('admin.verification-requirements.*') ? 'bg-gray-900 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}"
        >
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>KYC Document Rules</span>
        </a>

        <a
            href="{{ route('admin.verification-locations.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ request()->routeIs('admin.verification-locations.*') ? 'bg-gray-900 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}"
        >
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Inspection Hubs</span>
        </a>

        <a
            href="{{ route('admin.verification-checklists.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ request()->routeIs('admin.verification-checklists.*') ? 'bg-gray-900 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}"
        >
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>Inspection Checklists</span>
        </a>

        <a
            href="{{ route('admin.verifiers.index') }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ request()->routeIs('admin.verifiers.*') ? 'bg-gray-900 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}"
        >
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Verifier Staff</span>
        </a>
    </div>
</div>
