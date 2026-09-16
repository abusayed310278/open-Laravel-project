@extends('layouts.admin')

@section('title', 'Orders')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Orders</x-slot:title>
        <x-slot:action>
            <div class="flex items-center gap-2.5">
                {{-- Bulk Delete Button --}}
                <button type="button" id="bulk-delete-btn" class="hidden items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-semibold text-xs rounded-lg transition cursor-pointer border border-red-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    <span>Delete Selected (<span id="selected-count">0</span>)</span>
                </button>

                {{-- Status Filter --}}
                <form method="GET" class="flex items-center">
                    <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white text-gray-700 hover:border-gray-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 cursor-pointer">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </x-slot:action>

        {{-- Hidden Bulk Delete Form --}}
        <form id="bulk-delete-form" method="POST" action="{{ route('admin.orders.bulk-delete') }}" class="hidden">
            @csrf
            @method('DELETE')
            <div id="bulk-delete-inputs"></div>
        </form>

        <x-table :headers="['<input type=\'checkbox\' id=\'select-all-orders\' class=\'w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 cursor-pointer\'>', 'Order #', 'Customer', 'Vendors', 'Date', 'Status', 'Total', 'Actions']" id="admin-orders-table">
            @forelse ($orders as $order)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 w-8">
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 cursor-pointer">
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-gray-900 font-mono hover:text-brand-600 flex items-center gap-1.5">
                            <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-800 font-semibold">{{ $order->order_number }}</span>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <div class="font-medium text-gray-900">{{ $order->customer?->name ?? 'Guest / Deleted' }}</div>
                        @if ($order->customer?->email)
                            <div class="text-xs text-gray-400">{{ $order->customer->email }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge color="blue">{{ $order->vendorOrders->count() }} {{ $order->vendorOrders->count() === 1 ? 'vendor' : 'vendors' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">${{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- View Icon --}}
                            <a href="{{ route('admin.orders.show', $order) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="View order details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </a>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" data-confirm="Delete this order record?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">No orders found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$orders" />
    </x-card>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAll = document.getElementById('select-all-orders');
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const bulkBtn = document.getElementById('bulk-delete-btn');
            const selectedCountSpan = document.getElementById('selected-count');
            const bulkForm = document.getElementById('bulk-delete-form');
            const bulkInputsContainer = document.getElementById('bulk-delete-inputs');

            function updateBulkState() {
                const checked = Array.from(checkboxes).filter(cb => cb.checked);
                const count = checked.length;

                if (count > 0) {
                    if (selectedCountSpan) selectedCountSpan.textContent = count;
                    if (bulkBtn) {
                        bulkBtn.classList.remove('hidden');
                        bulkBtn.classList.add('inline-flex');
                    }
                } else {
                    if (bulkBtn) {
                        bulkBtn.classList.add('hidden');
                        bulkBtn.classList.remove('inline-flex');
                    }
                }

                if (selectAll) {
                    selectAll.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
                    selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkState();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkState);
            });

            if (bulkBtn && bulkForm) {
                bulkBtn.addEventListener('click', () => {
                    const checked = Array.from(checkboxes).filter(cb => cb.checked);
                    if (checked.length === 0) return;

                    const confirmMsg = `Are you sure you want to delete ${checked.length} selected order(s)? This action cannot be undone.`;
                    if (!confirm(confirmMsg)) return;

                    bulkInputsContainer.innerHTML = '';
                    checked.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkInputsContainer.appendChild(input);
                    });

                    bulkForm.submit();
                });
            }
        });
    </script>
@endsection
