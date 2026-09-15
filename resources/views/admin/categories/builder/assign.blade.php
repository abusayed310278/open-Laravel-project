@extends('layouts.admin')

@section('title', 'Category Builder — Assign to Category')

@section('content')
    @php($active = 'assign')
    @include('admin.categories.builder._tabs')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="assign-workspace">
        {{-- Left: Category Picker Tree --}}
        <div class="lg:col-span-4 space-y-3">
            <x-card>
                <x-slot:title>
                    <div class="flex items-center justify-between w-full">
                        <span>Select Category</span>
                        <span class="text-xs text-gray-400">Step 1</span>
                    </div>
                </x-slot:title>

                <div class="mb-3">
                    <input type="text" id="cat-tree-search" placeholder="Search category..."
                           class="w-full text-sm rounded-md border border-gray-200 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="divide-y divide-gray-50 max-h-[640px] overflow-y-auto pr-1" id="cat-picker-list">
                    @foreach ($tree as $node)
                        <button type="button"
                                class="w-full text-left py-2 px-2 hover:bg-gray-50 rounded flex items-center justify-between gap-2 text-sm transition cat-picker-item {{ $selectedCategoryId === $node['id'] ? 'bg-brand-50 text-brand-700 font-semibold ring-1 ring-brand-500' : 'text-gray-800' }}"
                                data-id="{{ $node['id'] }}"
                                data-name="{{ $node['name'] }}"
                                data-search="{{ strtolower($node['name']) }}"
                                style="padding-left: {{ max(8, $node['depth'] * 18 + 8) }}px">
                            <span class="truncate flex items-center gap-1.5 min-w-0">
                                @if ($node['depth'] > 0)
                                    <span class="text-gray-300 select-none font-mono text-xs">└</span>
                                @endif
                                <span class="truncate">{{ $node['name'] }}</span>
                            </span>

                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full shrink-0 cat-attr-counter-{{ $node['id'] }} {{ ($node['attributes_count'] ?? 0) > 0 ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-400' }}">
                                {{ $node['attributes_count'] ?? 0 }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </x-card>
        </div>

        {{-- Right: Grouped Attributes Checklist for Selected Category --}}
        <div class="lg:col-span-8">
            <div id="no-category-selected" class="{{ $selectedCategoryId ? 'hidden' : '' }}">
                <x-card>
                    <div class="py-16 text-center text-gray-400 space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-lg">
                            ←
                        </div>
                        <h3 class="text-base font-semibold text-gray-700">Select a category on the left</h3>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">Click any category from the tree to view and customize which specifications and attributes apply to it.</p>
                    </div>
                </x-card>
            </div>

            <div id="category-assign-panel" class="{{ ! $selectedCategoryId ? 'hidden' : '' }}">
                <form method="POST" id="sync-form" action="">
                    @csrf
                    <input type="hidden" name="redirect_to" id="redirect-input" value="{{ request()->fullUrl() }}">

                    <x-card>
                        <x-slot:title>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900" id="selected-cat-title">Attributes for Selected Category</span>
                                    <span id="selected-cat-badge" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-50 text-brand-600"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="#" id="view-dedicated-link" class="text-xs text-brand-600 hover:underline">
                                        Advanced Pivot Settings →
                                    </a>
                                </div>
                            </div>
                        </x-slot:title>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <input type="text" id="checklist-search" placeholder="Search attributes in groups..."
                                   class="w-full sm:w-72 text-sm rounded-md border border-gray-200 px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-brand-500">

                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-select-all" class="text-xs font-medium text-gray-600 hover:text-gray-900 px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 transition">
                                    Check Visible
                                </button>
                                <button type="button" id="btn-deselect-all" class="text-xs font-medium text-gray-600 hover:text-gray-900 px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 transition">
                                    Uncheck Visible
                                </button>
                            </div>
                        </div>

                        {{-- Diff status bar --}}
                        <div id="diff-bar" class="hidden mb-4 p-3 bg-amber-50 border border-amber-200 rounded-md flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 text-amber-800 font-medium">
                                <span>Unsaved changes:</span>
                                <span id="diff-added-text" class="text-green-700 font-bold">+0 added</span>
                                <span id="diff-removed-text" class="text-red-700 font-bold">-0 removed</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-reset-diff" class="text-gray-500 hover:text-gray-700 underline">Reset</button>
                                <x-button type="submit" size="sm">Save Assignments</x-button>
                            </div>
                        </div>

                        {{-- Grouped Attributes Container --}}
                        <div class="space-y-6 max-h-[600px] overflow-y-auto pr-1" id="groups-container">
                            @foreach ($groupedAttributes as $group)
                                <div class="border border-gray-100 rounded-lg p-4 bg-gray-50/50 group-card" data-group-id="{{ $group['group_id'] }}">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-bold text-gray-800">{{ $group['group_name'] }}</h3>
                                            <span class="text-xs text-gray-400">({{ count($group['attributes']) }})</span>
                                        </div>
                                        <button type="button" class="text-[11px] font-medium text-brand-600 hover:underline btn-group-toggle" data-group-id="{{ $group['group_id'] }}">
                                            Toggle Group
                                        </button>
                                    </div>

                                    <div class="grid sm:grid-cols-2 gap-2">
                                        @foreach ($group['attributes'] as $attr)
                                            <label class="flex items-start gap-2.5 p-2 rounded-md bg-white border border-gray-100 hover:border-brand-200 cursor-pointer text-xs transition attr-checkbox-label"
                                                   data-attr-id="{{ $attr['id'] }}"
                                                   data-attr-name="{{ strtolower($attr['name']) }}">
                                                <input type="checkbox" name="attribute_ids[]" value="{{ $attr['id'] }}"
                                                       class="w-4 h-4 mt-0.5 rounded accent-brand-500 attr-check"
                                                       data-attr-id="{{ $attr['id'] }}">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <span class="font-medium text-gray-900 truncate">{{ $attr['name'] }}</span>
                                                        <span class="text-[10px] text-gray-400 font-mono">{{ $attr['type'] }}</span>
                                                    </div>
                                                    @if ($attr['unit'])
                                                        <div class="text-[10px] text-gray-400">Unit: {{ $attr['unit'] }}</div>
                                                    @endif
                                                    @if (! empty($attr['values']))
                                                        <div class="text-[10px] text-gray-500 truncate mt-0.5">
                                                            {{ implode(', ', array_slice($attr['values'], 0, 3)) }}{{ count($attr['values']) > 3 ? '...' : '' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Bottom Save Bar --}}
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500" id="selection-summary">No changes yet.</span>
                            <x-button type="submit">Save Attribute Assignments</x-button>
                        </div>
                    </x-card>
                </form>
            </div>
        </div>
    </div>

    {{-- State & Client-side Script --}}
    <script>
        const assignmentsData = {{ Js::from($assignments) }};
        let currentCategoryId = {{ Js::from($selectedCategoryId) }};
        let originalIds = new Set();
        let selectedIds = new Set();

        const noCatPanel = document.getElementById('no-category-selected');
        const assignPanel = document.getElementById('category-assign-panel');
        const syncForm = document.getElementById('sync-form');
        const catTitle = document.getElementById('selected-cat-title');
        const catBadge = document.getElementById('selected-cat-badge');
        const dedicatedLink = document.getElementById('view-dedicated-link');
        const diffBar = document.getElementById('diff-bar');
        const diffAddedText = document.getElementById('diff-added-text');
        const diffRemovedText = document.getElementById('diff-removed-text');
        const selectionSummary = document.getElementById('selection-summary');

        function initCategory(categoryId, categoryName) {
            currentCategoryId = categoryId;
            const assigned = assignmentsData[categoryId] || [];
            originalIds = new Set(assigned.map(Number));
            selectedIds = new Set(originalIds);

            // Update UI
            noCatPanel.classList.add('hidden');
            assignPanel.classList.remove('hidden');

            catTitle.textContent = `Attributes for ${categoryName}`;
            catBadge.textContent = `${assigned.length} currently assigned`;
            syncForm.action = `/admin/categories/${categoryId}/attributes/sync`;
            dedicatedLink.href = `/admin/categories/${categoryId}/attributes`;

            // Highlight in tree
            document.querySelectorAll('.cat-picker-item').forEach(btn => {
                const isSelected = Number(btn.dataset.id) === Number(categoryId);
                btn.classList.toggle('bg-brand-50', isSelected);
                btn.classList.toggle('text-brand-700', isSelected);
                btn.classList.toggle('font-semibold', isSelected);
                btn.classList.toggle('ring-1', isSelected);
                btn.classList.toggle('ring-brand-500', isSelected);
            });

            // Update checkboxes
            document.querySelectorAll('.attr-check').forEach(cb => {
                const id = Number(cb.dataset.attrId);
                cb.checked = selectedIds.has(id);
            });

            updateDiff();
        }

        function updateDiff() {
            let addedCount = 0;
            let removedCount = 0;

            selectedIds.forEach(id => {
                if (!originalIds.has(id)) addedCount++;
            });

            originalIds.forEach(id => {
                if (!selectedIds.has(id)) removedCount++;
            });

            const hasDiff = addedCount > 0 || removedCount > 0;
            diffBar.classList.toggle('hidden', !hasDiff);

            diffAddedText.textContent = `+${addedCount} added`;
            diffRemovedText.textContent = `-${removedCount} removed`;

            if (hasDiff) {
                selectionSummary.textContent = `${selectedIds.size} attributes will be assigned (+${addedCount}, -${removedCount}).`;
            } else {
                selectionSummary.textContent = `${selectedIds.size} attributes assigned (in sync).`;
            }

            // Highlight label tags
            document.querySelectorAll('.attr-checkbox-label').forEach(label => {
                const id = Number(label.dataset.attrId);
                const isSelected = selectedIds.has(id);
                const wasOriginal = originalIds.has(id);

                label.classList.toggle('border-green-300', isSelected && !wasOriginal);
                label.classList.toggle('bg-green-50/40', isSelected && !wasOriginal);
                label.classList.toggle('border-red-300', !isSelected && wasOriginal);
                label.classList.toggle('bg-red-50/40', !isSelected && wasOriginal);
                label.classList.toggle('border-brand-200', isSelected && wasOriginal);
            });
        }

        // Category tree item click
        document.querySelectorAll('.cat-picker-item').forEach(btn => {
            btn.addEventListener('click', () => {
                initCategory(Number(btn.dataset.id), btn.dataset.name);
            });
        });

        // Checkbox change listener
        document.querySelectorAll('.attr-check').forEach(cb => {
            cb.addEventListener('change', () => {
                const id = Number(cb.dataset.attrId);
                if (cb.checked) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                updateDiff();
            });
        });

        // Group toggle
        document.querySelectorAll('.btn-group-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.group-card');
                const checks = Array.from(card.querySelectorAll('.attr-check:not([disabled])'));
                const allChecked = checks.every(c => c.checked);

                checks.forEach(c => {
                    const id = Number(c.dataset.attrId);
                    c.checked = !allChecked;
                    if (!allChecked) selectedIds.add(id);
                    else selectedIds.delete(id);
                });
                updateDiff();
            });
        });

        // Select visible / Deselect visible
        document.getElementById('btn-select-all')?.addEventListener('click', () => {
            document.querySelectorAll('.attr-checkbox-label:not([style*="display: none"]) .attr-check').forEach(cb => {
                cb.checked = true;
                selectedIds.add(Number(cb.dataset.attrId));
            });
            updateDiff();
        });

        document.getElementById('btn-deselect-all')?.addEventListener('click', () => {
            document.querySelectorAll('.attr-checkbox-label:not([style*="display: none"]) .attr-check').forEach(cb => {
                cb.checked = false;
                selectedIds.delete(Number(cb.dataset.attrId));
            });
            updateDiff();
        });

        // Reset diff
        document.getElementById('btn-reset-diff')?.addEventListener('click', () => {
            selectedIds = new Set(originalIds);
            document.querySelectorAll('.attr-check').forEach(cb => {
                cb.checked = selectedIds.has(Number(cb.dataset.attrId));
            });
            updateDiff();
        });

        // Category tree search
        document.getElementById('cat-tree-search')?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.cat-picker-item').forEach(item => {
                const search = item.dataset.search || '';
                item.style.display = query === '' || search.includes(query) ? 'flex' : 'none';
            });
        });

        // Checklist attributes search
        document.getElementById('checklist-search')?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.group-card').forEach(card => {
                let groupHasMatch = false;
                card.querySelectorAll('.attr-checkbox-label').forEach(label => {
                    const name = label.dataset.attrName || '';
                    const match = query === '' || name.includes(query);
                    label.style.display = match ? 'flex' : 'none';
                    if (match) groupHasMatch = true;
                });
                card.style.display = groupHasMatch ? 'block' : 'none';
            });
        });

        // Boot if initial category present
        if (currentCategoryId) {
            const activeBtn = document.querySelector(`.cat-picker-item[data-id="${currentCategoryId}"]`);
            if (activeBtn) {
                initCategory(currentCategoryId, activeBtn.dataset.name);
            }
        }
    </script>
@endsection
