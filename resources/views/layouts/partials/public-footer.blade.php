<footer style="background-color: #0c0f1d; color: #94a3b8;" class="text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-2 md:grid-cols-5 gap-8 lg:gap-10">
        {{-- Brand & About --}}
        <div class="col-span-2 md:col-span-1">
            <a href="{{ route('home') }}" class="inline-block mb-4">
                <x-brand-logo :dark="true" />
            </a>
            <p class="text-xs text-gray-400 leading-relaxed mb-4">
                Bangladesh's premier verified electronics marketplace. Every device inspected, graded, and warehoused for authentic peace of mind.
            </p>
            <p class="text-xs text-gray-500 font-medium">Dhaka, Bangladesh</p>
        </div>

        {{-- Marketplace --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Marketplace</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                <li><a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="hover:text-amber-400 transition-colors">Shop All</a></li>
                <li><a href="{{ Route::has('shop') ? route('shop', ['category' => 'phones']) : '#' }}" class="hover:text-amber-400 transition-colors">Phones</a></li>
                <li><a href="{{ Route::has('shop') ? route('shop', ['category' => 'laptops']) : '#' }}" class="hover:text-amber-400 transition-colors">Laptops</a></li>
                <li><a href="{{ Route::has('shop') ? route('shop', ['condition' => 'refurbished']) : '#' }}" class="hover:text-amber-400 transition-colors">Refurbished Deals</a></li>
                <li><a href="{{ Route::has('grading-system') ? route('grading-system') : '#' }}" class="hover:text-amber-400 transition-colors">Grading Guide</a></li>
            </ul>
        </div>

        {{-- Sell --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Sell</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                <li><a href="{{ Route::has('register.saler') ? route('register.saler') : (Route::has('register') ? route('register') : '#') }}" class="hover:text-amber-400 transition-colors">Sell as Saler</a></li>
                <li><a href="{{ Route::has('register.business') ? route('register.business') : (Route::has('register') ? route('register') : '#') }}" class="hover:text-amber-400 transition-colors">Sell as Business</a></li>
                <li><a href="{{ Route::has('stores.index') ? route('stores.index') : '#' }}" class="hover:text-amber-400 transition-colors">Browse Stores</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Warehouse Custody</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Fee Calculator</a></li>
            </ul>
        </div>

        {{-- Support --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Support</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                <li><a href="#" class="hover:text-amber-400 transition-colors">Help Center</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Track Order</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Returns &amp; Refund Policy</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Escrow Protection</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Contact Support</a></li>
            </ul>
        </div>

        {{-- Company --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Company</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                <li><a href="#" class="hover:text-amber-400 transition-colors">About Openbox</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Terms of Service</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Privacy Policy</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Trust &amp; Safety</a></li>
                <li><a href="#" class="hover:text-amber-400 transition-colors">Careers</a></li>
            </ul>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div style="background-color: #070913; border-top: 1px solid #1e293b;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <span>&copy; {{ now()->year }} Openbox. All rights reserved.</span>
            <div class="flex items-center gap-4 text-gray-400">
                <span>100% Inspected Electronics</span>
                <span>•</span>
                <span>Escrow Secured</span>
                <span>•</span>
                <span>Doorstep Verification</span>
            </div>
        </div>
    </div>
</footer>
