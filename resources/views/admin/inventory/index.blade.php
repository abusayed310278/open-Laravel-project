@extends('layouts.admin')

@section('title', 'Inventory')

@section('content')
    <x-card title="Movement Ledger">
        <x-table :headers="['Product', 'Type', 'Change', 'By', 'Date']" id="admin-inventory-table">
            @forelse ($movements as $movement)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $movement->product->title }}</td>
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
    </x-card>
@endsection
