@extends('layouts.admin')

@section('title', 'Social Types')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Social Types</h1>
            <p class="text-sm text-gray-500 mt-1">Manage system social type platforms, SVG icons, and order.</p>
        </div>
        <div>
            <button
                type="button"
                onclick="openAddModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow-sm transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add Social Type</span>
            </button>
        </div>
    </div>

    {{-- Flash Status Message --}}
    @if (session('status'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-sm font-medium text-green-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Main Content Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        {{-- Table Toolbar --}}
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Search Input --}}
            <form action="{{ route('admin.social-types.index') }}" method="GET" class="w-full sm:w-72">
                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search social types..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:bg-white transition-all"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </form>

            {{-- Export Buttons --}}
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button
                    type="button"
                    onclick="copyTableData()"
                    class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-xs font-semibold text-gray-700 transition-colors shadow-2xs cursor-pointer"
                    id="copy-btn"
                >
                    Copy
                </button>
                <a
                    href="{{ route('admin.social-types.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                    class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-xs font-semibold text-gray-700 transition-colors shadow-2xs"
                >
                    CSV
                </a>
                <a
                    href="{{ route('admin.social-types.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                    class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-xs font-semibold text-gray-700 transition-colors shadow-2xs"
                >
                    Excel
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm" id="social-types-table">
                <thead class="bg-gray-50/60 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Type</th>
                        <th class="px-6 py-3.5">Icon</th>
                        <th class="px-6 py-3.5 text-center">Order</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($socialTypes as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors" data-id="{{ $item->id }}">
                            {{-- Type Name --}}
                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $item->name }}
                            </td>

                            {{-- Icon Preview & Class --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-700 overflow-hidden flex-shrink-0">
                                        @if ($item->icon_svg)
                                            {!! $item->icon_svg !!}
                                        @elseif ($item->icon)
                                            <i class="{{ $item->icon }} text-base"></i>
                                        @else
                                            <span class="text-xs font-bold text-gray-400">#</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-xs text-gray-500 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">{{ $item->icon ?: 'custom-svg' }}</span>
                                </div>
                            </td>

                            {{-- Order --}}
                            <td class="px-6 py-4 text-center font-medium text-gray-600">
                                {{ $item->order }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-center">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200/60">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        onclick="openEditModal({{ json_encode($item) }})"
                                        class="p-1.5 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors cursor-pointer"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>

                                    {{-- Delete Form --}}
                                    <form method="POST" action="{{ route('admin.social-types.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this social type?')" class="inline-block m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                            title="Delete"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                No social types configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($socialTypes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $socialTypes->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Add / Edit Modal --}}
<div id="social-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 id="modal-title" class="text-base font-bold text-gray-900">Add Social Type</h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="social-form" method="POST" action="{{ route('admin.social-types.store') }}" class="p-6 space-y-4">
            @csrf
            <div id="method-container"></div>

            {{-- Name --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Platform Name <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="name"
                    id="modal-name"
                    required
                    placeholder="e.g. Facebook, YouTube, Twitter"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
            </div>

            {{-- Icon Class --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Icon Class (FontAwesome / Tag)</label>
                <input
                    type="text"
                    name="icon"
                    id="modal-icon"
                    placeholder="e.g. fa-brands fa-square-facebook"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
            </div>

            {{-- Optional SVG --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Inline SVG Code (Optional)</label>
                <textarea
                    name="icon_svg"
                    id="modal-icon-svg"
                    rows="2"
                    placeholder="<svg ...>...</svg>"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                ></textarea>
            </div>

            {{-- Order & Status --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Display Order</label>
                    <input
                        type="number"
                        name="order"
                        id="modal-order"
                        min="0"
                        placeholder="1"
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                    >
                </div>
                <div class="flex items-center pt-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" id="modal-active" value="1" checked class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button
                    type="button"
                    onclick="closeModal()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm font-semibold text-gray-700 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="modal-submit-btn"
                    class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer"
                >
                    Save Platform
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modal-title').innerText = 'Add Social Type';
        document.getElementById('modal-submit-btn').innerText = 'Save Platform';
        const form = document.getElementById('social-form');
        form.action = "{{ route('admin.social-types.store') }}";
        document.getElementById('method-container').innerHTML = '';
        document.getElementById('modal-name').value = '';
        document.getElementById('modal-icon').value = '';
        document.getElementById('modal-icon-svg').value = '';
        document.getElementById('modal-order').value = '';
        document.getElementById('modal-active').checked = true;
        document.getElementById('social-modal').classList.remove('hidden');
    }

    function openEditModal(item) {
        document.getElementById('modal-title').innerText = 'Edit Social Type';
        document.getElementById('modal-submit-btn').innerText = 'Update Platform';
        const form = document.getElementById('social-form');
        form.action = "/admin/social-types/" + item.id;
        document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('modal-name').value = item.name || '';
        document.getElementById('modal-icon').value = item.icon || '';
        document.getElementById('modal-icon-svg').value = item.icon_svg || '';
        document.getElementById('modal-order').value = item.order ?? '';
        document.getElementById('modal-active').checked = !!item.is_active;
        document.getElementById('social-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('social-modal').classList.add('hidden');
    }

    function copyTableData() {
        const rows = document.querySelectorAll('#social-types-table tbody tr');
        let text = "Type\tIcon\tOrder\tStatus\n";
        rows.forEach(r => {
            const cols = r.querySelectorAll('td');
            if (cols.length >= 4) {
                text += `${cols[0].innerText.trim()}\t${cols[1].innerText.trim()}\t${cols[2].innerText.trim()}\t${cols[3].innerText.trim()}\n`;
            }
        });
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copy-btn');
            const orig = btn.innerText;
            btn.innerText = 'Copied!';
            setTimeout(() => { btn.innerText = orig; }, 1800);
        });
    }
</script>
@endsection
