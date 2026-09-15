<div id="drawer-overlay" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden"></div>

<aside id="mobile-drawer" class="fixed inset-y-0 left-0 w-72 bg-white z-50 transform -translate-x-full transition-transform duration-300 lg:hidden flex flex-col">
    <div class="flex items-center justify-between px-5 py-5 border-b border-gray-100">
        <a href="{{ route('home') }}">
            <x-brand-logo />
        </a>
        <button id="drawer-close" type="button" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm font-medium text-gray-600">
        <a href="{{ route('categories.index') }}" class="block px-3 py-2 rounded-md bg-brand-50/70 text-brand-700 font-bold hover:bg-brand-100">All Categories</a>
        <a href="{{ route('shop') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900 font-semibold text-gray-900">All Products</a>
        <a href="{{ route('shop', ['category' => 'desktop-computers']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Desktop</a>
        <a href="{{ route('shop', ['category' => 'laptops']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Laptop</a>
        <a href="{{ route('shop', ['category' => 'components']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Component</a>
        <a href="{{ route('shop', ['category' => 'monitor']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Monitor</a>
        <a href="{{ route('shop', ['category' => 'power']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Power</a>
        <a href="{{ route('shop', ['category' => 'smartphones']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Phone</a>
        <a href="{{ route('shop', ['category' => 'tablets']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Tablet</a>
        <a href="{{ route('shop', ['category' => 'office-equipment']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Office Equipment</a>
        <a href="{{ route('shop', ['category' => 'cameras']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Camera</a>
        <a href="{{ route('shop', ['category' => 'security']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Security</a>
        <a href="{{ route('shop', ['category' => 'networking']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Networking</a>
        <a href="{{ route('shop', ['category' => 'software']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Software</a>
        <a href="{{ route('shop', ['category' => 'server-storage']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Server & Storage</a>
        <a href="{{ route('shop', ['category' => 'accessories']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Accessories</a>
        <a href="{{ route('shop', ['category' => 'gadgets']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Gadget</a>
        <a href="{{ route('shop', ['category' => 'gaming-consoles']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Gaming</a>
        <a href="{{ route('shop', ['category' => 'television']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">TV</a>
        <a href="{{ route('shop', ['category' => 'appliance']) }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 hover:text-gray-900">Appliance</a>
    </nav>

    <div class="px-5 py-4 border-t border-gray-100 space-y-2">
        @auth
            <a href="{{ Route::has('account.dashboard') ? route('account.dashboard') : '#' }}" class="block text-center border border-gray-200 rounded-md py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">My Account</a>
        @else
            <a href="{{ Route::has('login') ? route('login') : '#' }}" class="block text-center border border-gray-200 rounded-md py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Log in</a>
        @endauth
        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="block text-center bg-brand-500 hover:bg-brand-600 text-white rounded-md py-2.5 text-sm font-semibold">Start Selling</a>
    </div>
</aside>
