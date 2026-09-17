@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Brands')

@section('content')
    @php
        $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
    @endphp

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Brands</x-slot:title>
        <x-slot:action>
            <x-button as="a" :href="route($portalPrefix . 'brands.create')" size="sm">Add Brand</x-button>
        </x-slot:action>

        <x-table :headers="['Name', 'Status', 'Actions']" id="brands-table">
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
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Status Icon --}}
                            <form method="POST" action="{{ route($portalPrefix . 'brands.toggle-status', $brand) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($brand->status === \App\Enums\PublishStatus::Active)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <a href="{{ route($portalPrefix . 'brands.edit', $brand) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit brand">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route($portalPrefix . 'brands.destroy', $brand) }}" data-confirm="Delete this brand?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete brand">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No brands found.</td></tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            <x-pagination :paginator="$brands" />
        </div>
    </x-card>
@endsection
