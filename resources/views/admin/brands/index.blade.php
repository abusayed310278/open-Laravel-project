@extends('layouts.admin')

@section('title', 'Brands')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Brands</x-slot:title>
        <x-slot:action>
            <x-button as="a" :href="route('admin.brands.create')" size="sm">Add Brand</x-button>
        </x-slot:action>

        <x-table :headers="['Name', 'Status', '']" id="brands-table">
            @forelse ($brands as $brand)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 flex items-center gap-3">
                        @if ($brand->logo)
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($brand->logo) }}" alt="{{ $brand->name }}" class="w-8 h-8 rounded object-contain bg-gray-50">
                        @endif
                        <span class="font-medium text-gray-900">{{ $brand->name }}</span>
                    </td>
                    <td class="px-4 py-3"><x-badge :color="$brand->status->value === 'active' ? 'green' : 'gray'">{{ $brand->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="text-brand-600 font-medium hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" data-confirm="Delete this brand?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-sm">No brands yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$brands" />
    </x-card>
@endsection
