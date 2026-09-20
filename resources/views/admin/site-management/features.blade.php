@extends('layouts.admin')

@section('title', 'Manage Whole Site — Feature Toggles')

@section('content')
<div class="space-y-6">
    @include('admin.site-management._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900 capitalize">
                {{ $group === 'homepage' ? 'Homepage Sections' : 'Product Page Components' }} Toggles
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Toggle section visibility on the live website in real-time without modifying code.</p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.site-management.features', ['group' => 'homepage']) }}"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $group === 'homepage' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
            >
                Homepage (14)
            </a>
            <a
                href="{{ route('admin.site-management.features', ['group' => 'product_page']) }}"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $group === 'product_page' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
            >
                Product Page (7)
            </a>
        </div>
    </div>

    {{-- Feature Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-gray-50/60 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Section Title & Key</th>
                        <th class="px-6 py-3.5">Description</th>
                        <th class="px-6 py-3.5 text-center">Order</th>
                        <th class="px-6 py-3.5 text-center">Live Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($features as $feature)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            {{-- Title & Key --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm mb-0.5">{{ $feature->title }}</div>
                                <span class="font-mono text-[11px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">{{ $feature->key }}</span>
                            </td>

                            {{-- Description --}}
                            <td class="px-6 py-4 text-xs text-gray-500 max-w-sm leading-relaxed">
                                {{ $feature->description }}
                            </td>

                            {{-- Sort Order --}}
                            <td class="px-6 py-4 text-center font-semibold text-gray-600">
                                {{ $feature->sort_order }}
                            </td>

                            {{-- Live Status Badge --}}
                            <td class="px-6 py-4 text-center">
                                @if ($feature->is_enabled)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-50 text-green-700 border border-green-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> Visible
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span> Hidden
                                    </span>
                                @endif
                            </td>

                            {{-- Actions (Quick Toggle Switch & Edit) --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    {{-- Quick Toggle Form --}}
                                    <form method="POST" action="{{ route('admin.site-management.features.toggle', $feature->key) }}" class="inline-block m-0">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs {{ $feature->is_enabled ? 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}"
                                            title="Click to toggle visibility"
                                        >
                                            @if ($feature->is_enabled)
                                                <span>Disable Section</span>
                                            @else
                                                <span>Enable Section</span>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Edit Modal Button --}}
                                    <button
                                        type="button"
                                        onclick="openEditFeatureModal({{ json_encode($feature) }})"
                                        class="p-1.5 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Feature Details"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                No feature toggles configured for {{ $group }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit Feature Modal --}}
<div id="feature-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">Edit Feature Section</h3>
            <button type="button" onclick="closeFeatureModal()" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="feature-form" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Section Title</label>
                <input
                    type="text"
                    name="title"
                    id="modal-feature-title"
                    required
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Description</label>
                <textarea
                    name="description"
                    id="modal-feature-description"
                    rows="3"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Display Order</label>
                    <input
                        type="number"
                        name="sort_order"
                        id="modal-feature-order"
                        min="0"
                        required
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                    >
                </div>
                <div class="flex items-center pt-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_enabled" id="modal-feature-enabled" value="1" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Enabled</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button
                    type="button"
                    onclick="closeFeatureModal()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm font-semibold text-gray-700 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditFeatureModal(feature) {
        const form = document.getElementById('feature-form');
        form.action = "/admin/site-management/features/" + feature.id;
        document.getElementById('modal-feature-title').value = feature.title || '';
        document.getElementById('modal-feature-description').value = feature.description || '';
        document.getElementById('modal-feature-order').value = feature.sort_order ?? 0;
        document.getElementById('modal-feature-enabled').checked = !!feature.is_enabled;
        document.getElementById('feature-modal').classList.remove('hidden');
    }

    function closeFeatureModal() {
        document.getElementById('feature-modal').classList.add('hidden');
    }
</script>
@endsection
