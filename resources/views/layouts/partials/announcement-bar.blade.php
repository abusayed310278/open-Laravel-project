<div class="bg-white text-black text-xs py-2 px-4 sm:px-6 border-b border-gray-200" style="background-color: #ffffff; color: #000000;">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        {{-- Left: Contact Info --}}
        <div class="flex items-center gap-6">
            <a href="mailto:info@openbox.ae" class="flex items-center gap-2 text-black hover:text-brand-600 transition-colors">
                <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                <span class="font-medium">info@openbox.ae</span>
            </a>
            <a href="tel:+971508079199" class="hidden sm:flex items-center gap-2 text-black hover:text-brand-600 transition-colors">
                <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                <span class="font-medium">+971 508079199</span>
            </a>
        </div>

        {{-- Right: Quick Links --}}
        <div class="flex items-center gap-6">
            <a href="{{ Route::has('stores.index') ? route('stores.index') : '#' }}" class="text-black hover:text-brand-600 transition-colors font-medium">Vendors</a>
            <a href="{{ Route::has('grading-system') ? route('grading-system') : '#' }}" class="text-black hover:text-brand-600 transition-colors font-medium">About</a>
        </div>
    </div>
</div>
