@extends('layouts.admin')

@section('title', 'Manage Whole Site — Dashboard Menu Customizer')

@section('content')
<div class="space-y-6">
    @include('admin.site-management._tabs')

    {{-- Role Selector Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Dynamic Dashboard Sidebar Customizer</h2>
            <p class="text-xs text-gray-500 mt-0.5">Customize navigation menus, headers, routes, and permissions for each portal sidebar.</p>
        </div>

        <button
            type="button"
            onclick="openAddMenuModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-xs transition-all cursor-pointer self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Menu Item</span>
        </button>
    </div>

    {{-- Role Selector Pills --}}
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
        @foreach ($roles as $role)
            <a
                href="{{ route('admin.site-management.menus', ['role' => $role->slug]) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $activeRoleSlug === $role->slug ? 'bg-gray-900 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}"
            >
                {{ $role->name }} Portal ({{ $role->slug }})
            </a>
        @endforeach
    </div>

    {{-- Menu Items Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm border-collapse">
                <thead class="bg-gray-50/60 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 w-64">Menu Title & Group</th>
                        <th class="px-4 py-3">Route / Target</th>
                        <th class="px-4 py-3">Required Permission</th>
                        <th class="px-3 py-3 text-center w-20">Order</th>
                        <th class="px-3 py-3 text-center w-24">Status</th>
                        <th class="px-4 py-3 text-right w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($menus as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            {{-- Title & Group --}}
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900 text-sm mb-0.5">{{ $item->title }}</div>
                                @if ($item->group_name)
                                    <span class="font-mono text-[10px] text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-100 font-bold uppercase tracking-wider">{{ $item->group_name }}</span>
                                @endif
                            </td>

                            {{-- Route --}}
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                {{ $item->route_name ?: ($item->url_path ?: '#') }}
                            </td>

                            {{-- Permission --}}
                            <td class="px-4 py-3">
                                @if ($item->permission_slug)
                                    <span class="font-mono text-[11px] text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100">{{ $item->permission_slug }}</span>
                                @else
                                    <span class="text-xs text-gray-400 font-italic">Public / Role Default</span>
                                @endif
                            </td>

                            {{-- Order --}}
                            <td class="px-3 py-3 text-center font-bold text-gray-600">
                                {{ $item->sort_order }}
                            </td>

                            {{-- Status --}}
                            <td class="px-3 py-3 text-center">
                                @if ($item->is_enabled)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200/60">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Disabled</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        onclick="openEditMenuModal({{ json_encode($item) }})"
                                        class="p-1 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Menu Item"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>

                                    {{-- Delete Form --}}
                                    <form method="POST" action="{{ route('admin.site-management.menus.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this menu item?')" class="inline-block m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                            title="Delete Menu Item"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                No menu items configured for {{ $activeRoleSlug }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add / Edit Menu Modal --}}
<div id="menu-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 id="menu-modal-title" class="text-base font-bold text-gray-900">Add Menu Item</h3>
            <button type="button" onclick="closeMenuModal()" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="menu-form" method="POST" action="{{ route('admin.site-management.menus.store') }}" class="p-6 space-y-4">
            @csrf
            <div id="menu-method-container"></div>
            <input type="hidden" name="role_slug" value="{{ $activeRoleSlug }}">

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Menu Title <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="title"
                    id="modal-menu-title"
                    required
                    placeholder="e.g. Products, Orders, Analytics"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Route Name</label>
                <input
                    type="text"
                    name="route_name"
                    id="modal-menu-route"
                    placeholder="e.g. business.products.index"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Menu Icon</label>
                <select
                    name="icon"
                    id="modal-menu-icon"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
                    <option value="">-- Auto-detect from Menu Title --</option>
                    <option value="home">Home / Dashboard (home)</option>
                    <option value="users">Users & Account (users)</option>
                    <option value="sliders">Sliders / Controls / Site Mgmt (sliders)</option>
                    <option value="box">Box / Product Catalog (box)</option>
                    <option value="tag">Tag / Categories & Brands (tag)</option>
                    <option value="shopping-bag">Shopping Bag / Orders (shopping-bag)</option>
                    <option value="banknotes">Banknotes / Invoices (banknotes)</option>
                    <option value="credit-card">Credit Card / Subscription / Payments (credit-card)</option>
                    <option value="wallet">Wallet / Payouts (wallet)</option>
                    <option value="building">Building / Store & Warehouse (building)</option>
                    <option value="shield">Shield / Verifications (shield)</option>
                    <option value="shield-check">Shield Check / Inspections & Security (shield-check)</option>
                    <option value="chat">Chat / Messages (chat)</option>
                    <option value="support">Support / Tickets (support)</option>
                    <option value="star">Star / Reviews & Feedback (star)</option>
                    <option value="heart">Heart / Wishlist (heart)</option>
                    <option value="undo">Undo / Return Requests (undo)</option>
                    <option value="map-pin">Map Pin / Addresses & Locations (map-pin)</option>
                    <option value="chart">Chart / Analytics & Reports (chart)</option>
                    <option value="cog">Cog / Settings (cog)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Sidebar Section Group Header</label>
                <input
                    type="text"
                    name="group_name"
                    id="modal-menu-group"
                    placeholder="e.g. CATALOG & SELLERS, COMMERCE, OVERVIEW"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Required Permission Slug</label>
                <select
                    name="permission_slug"
                    id="modal-menu-permission"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
                    <option value="">-- No Permission Required (Default) --</option>
                    @foreach ($permissions as $perm)
                        <option value="{{ $perm->slug }}">{{ $perm->name }} ({{ $perm->slug }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Sort Order</label>
                    <input
                        type="number"
                        name="sort_order"
                        id="modal-menu-order"
                        min="0"
                        placeholder="1"
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                    >
                </div>
                <div class="flex items-center pt-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_enabled" id="modal-menu-enabled" value="1" checked class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button
                    type="button"
                    onclick="closeMenuModal()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm font-semibold text-gray-700 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="modal-menu-submit-btn"
                    class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer"
                >
                    Save Menu Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddMenuModal() {
        document.getElementById('menu-modal-title').innerText = 'Add Menu Item';
        document.getElementById('modal-menu-submit-btn').innerText = 'Save Menu Item';
        const form = document.getElementById('menu-form');
        form.action = "{{ route('admin.site-management.menus.store') }}";
        document.getElementById('menu-method-container').innerHTML = '';
        document.getElementById('modal-menu-title').value = '';
        document.getElementById('modal-menu-route').value = '';
        document.getElementById('modal-menu-icon').value = '';
        document.getElementById('modal-menu-group').value = '';
        document.getElementById('modal-menu-permission').value = '';
        document.getElementById('modal-menu-order').value = '';
        document.getElementById('modal-menu-enabled').checked = true;
        document.getElementById('menu-modal').classList.remove('hidden');
    }

    function openEditMenuModal(item) {
        document.getElementById('menu-modal-title').innerText = 'Edit Menu Item';
        document.getElementById('modal-menu-submit-btn').innerText = 'Update Menu Item';
        const form = document.getElementById('menu-form');
        form.action = "/admin/site-management/menus/" + item.id;
        document.getElementById('menu-method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('modal-menu-title').value = item.title || '';
        document.getElementById('modal-menu-route').value = item.route_name || '';
        document.getElementById('modal-menu-icon').value = item.icon || '';
        document.getElementById('modal-menu-group').value = item.group_name || '';
        document.getElementById('modal-menu-permission').value = item.permission_slug || '';
        document.getElementById('modal-menu-order').value = item.sort_order ?? 0;
        document.getElementById('modal-menu-enabled').checked = !!item.is_enabled;
        document.getElementById('menu-modal').classList.remove('hidden');
    }

    function closeMenuModal() {
        document.getElementById('menu-modal').classList.add('hidden');
    }
</script>
@endsection
