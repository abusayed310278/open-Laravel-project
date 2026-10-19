@extends('layouts.admin')

@section('title', 'Inventory')

@section('content')
    <x-card title="Movement Ledger">
        {{-- Live Search Toolbar --}}
        <div class="mb-5 flex items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex items-center gap-2 w-full sm:w-80">
                <div class="relative w-full">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search product, SKU, user, notes..."
                        autocomplete="off"
                        class="w-full text-xs pl-8 pr-8 py-2 border border-gray-200 rounded-lg focus:outline-hidden focus:border-brand-500 bg-white"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    @if (request()->filled('search'))
                        <a href="{{ route('admin.inventory.index') }}" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs search-clear-btn" title="Clear">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div id="search-results-wrapper">
        <x-table :headers="['Product', 'Type', 'Change', 'By', 'Date']" id="admin-inventory-table">
            @forelse ($movements as $movement)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $movement->product?->title ?? 'Deleted Product' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $movement->type->label() }}</td>
                    <td class="px-4 py-3 {{ $movement->quantity >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                        {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $movement->createdBy?->name ?? 'System' }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $movement->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No inventory movements yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$movements" />
        </div>
    </x-card>
@endsection
