@extends('layouts.verifier')

@section('title', 'Inspection Queue & Appointments')

@section('content')
    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Active Inspection Queue</h1>
            <p class="text-xs sm:text-sm text-gray-500">Scheduled devices awaiting physical examination and grading at your hub.</p>
        </div>
        <a href="{{ route('verifier.history.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>View Past Inspections</span>
        </a>
    </div>

    {{-- Filter & Search Toolbar --}}
    <x-card>
        <form method="GET" action="{{ route('verifier.appointments.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by product title, SKU, or seller..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="w-44">
                <select name="status" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Active Statuses</option>
                    <option value="scheduled" {{ $currentStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="inspecting" {{ $currentStatus === 'inspecting' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                Filter
            </button>

            @if ($search || $currentStatus)
                <a href="{{ route('verifier.appointments.index') }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-card>
        <x-table :headers="['Product Details', 'Seller', 'Inspection Hub', 'Scheduled For', 'Status', 'Action']" id="appointments-table">
            @forelse ($appointments as $appointment)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5 font-medium text-gray-900">
                        <div class="flex items-center gap-3">
                            <img src="{{ $appointment->product->primaryImageUrl() }}" alt="{{ $appointment->product->title }}" class="w-11 h-11 object-cover rounded-lg border border-gray-100 flex-shrink-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $appointment->product->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $appointment->product->category?->name ?? 'Electronics' }}
                                    @if ($appointment->product->sku)
                                        · SKU: <span class="font-mono text-gray-600">{{ $appointment->product->sku }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        <p class="font-semibold text-gray-900">{{ $appointment->seller->name }}</p>
                        <p class="text-gray-400">{{ $appointment->seller->email }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        <p class="font-medium text-gray-800">{{ $appointment->location?->name ?? 'Default Hub' }}</p>
                        <p class="text-[11px] text-gray-400">{{ $appointment->location?->city ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-700">
                        <p class="font-semibold">{{ $appointment->scheduled_at?->format('M j, Y') }}</p>
                        <p class="text-gray-400">{{ $appointment->scheduled_at?->format('g:i A') }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <x-badge :color="$appointment->status->badgeColor()">{{ $appointment->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1.5">
                            <form method="POST" action="{{ route('chat.start', $appointment->product) }}" class="inline-block">
                                @csrf
                                <button type="submit" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg border border-gray-200 transition cursor-pointer" title="Message {{ $appointment->seller->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                            </form>
                            <a href="{{ route('verifier.appointments.inspect', $appointment) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                                <span>{{ $appointment->status->value === 'inspecting' ? 'Resume' : 'Inspect' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        No appointments currently match your filter.
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            <x-pagination :paginator="$appointments" />
        </div>
    </x-card>
@endsection
