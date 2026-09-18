@extends('layouts.verifier')

@section('title', 'Verifier Dashboard')

@section('content')
    {{-- Top Hub Banner --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-950">Welcome, {{ $verifier->name }}</h1>
                <p class="text-xs sm:text-sm text-gray-500">
                    Stationed Hub:
                    <span class="font-semibold text-gray-800">{{ $location?->name ?? 'Global Inspection Center' }}</span>
                    @if ($location?->city)
                        · {{ $location->city }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('verifier.appointments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                <span>View Inspection Queue</span>
            </a>
            <a href="{{ route('verifier.history.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors">
                <span>History</span>
            </a>
        </div>
    </div>

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <x-stat-card
            label="Today's Appointments"
            :value="$todayAppointmentsCount"
            hint="Scheduled for today"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        />

        <x-stat-card
            label="Active Queue"
            :value="$pendingInspectionsCount"
            hint="Awaiting inspection"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card
            label="Completed This Month"
            :value="$completedThisMonthCount"
            hint="Inspected & graded"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card
            label="Pass Rate"
            :value="$passRate . '%'"
            hint="Certified quality standard"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
        />
    </div>

    {{-- Today's Scheduled Inspections --}}
    <x-card title="Today's Scheduled Appointments">
        <x-table :headers="['Product', 'Seller', 'Scheduled Time', 'Status', '']" id="today-appointments-table">
            @forelse ($todayAppointments as $item)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5 font-medium text-gray-900">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item->product->primaryImageUrl() }}" alt="{{ $item->product->title }}" class="w-10 h-10 object-cover rounded-lg border border-gray-100 flex-shrink-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $item->product->title }}</p>
                                <p class="text-xs text-gray-400">{{ $item->product->category?->name ?? 'Electronics' }} · SKU: {{ $item->product->sku ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        <p class="font-medium text-gray-900">{{ $item->seller->name }}</p>
                        <p class="text-gray-400">{{ $item->seller->email }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-700 font-medium">
                        {{ $item->scheduled_at?->format('g:i A') ?? 'Scheduled Today' }}
                    </td>
                    <td class="px-4 py-3.5">
                        <x-badge :color="$item->status->badgeColor()">{{ $item->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('verifier.appointments.inspect', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                            <span>{{ $item->status->value === 'inspecting' ? 'Continue Inspection' : 'Start Inspection' }}</span>
                            <span>→</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-xs sm:text-sm">
                        No appointments scheduled for today yet.
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    {{-- Recent Inspection Activity --}}
    <x-card title="Recently Completed Inspections">
        <x-table :headers="['Product', 'Seller', 'Assigned Grade', 'Result', 'Inspected On']" id="recent-inspections-table">
            @forelse ($recentInspections as $recent)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5 font-medium text-gray-900 text-sm">
                        <div class="flex items-center gap-3">
                            <img src="{{ $recent->product->primaryImageUrl() }}" alt="{{ $recent->product->title }}" class="w-9 h-9 object-cover rounded-lg border border-gray-100 flex-shrink-0">
                            <div>
                                <p class="text-xs sm:text-sm font-semibold text-gray-900 line-clamp-1">{{ $recent->product->title }}</p>
                                <p class="text-[11px] text-gray-400">{{ $recent->product->category?->name ?? 'Electronics' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        {{ $recent->seller->name }}
                    </td>
                    <td class="px-4 py-3.5">
                        @if ($recent->gradeAssignment)
                            @php
                                $gradeEnum = $recent->gradeAssignment->grade instanceof \App\Enums\ProductGrade 
                                    ? $recent->gradeAssignment->grade 
                                    : \App\Enums\ProductGrade::tryFrom((string)$recent->gradeAssignment->grade);
                                $gradeVal = $gradeEnum?->value ?? (string)$recent->gradeAssignment->grade;
                                $gradeStyle = $gradeEnum?->badgeClass() ?? 'bg-gray-100 text-gray-700 border border-gray-200';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $gradeStyle }}">
                                Grade {{ $gradeVal }}
                            </span>
                        @elseif ($recent->status->value === 'rejected')
                            <span class="text-xs text-red-500 font-medium">Rejected</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <x-badge :color="$recent->status->badgeColor()">{{ $recent->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-500">
                        {{ $recent->inspected_at?->diffForHumans() ?? 'Recently' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-xs sm:text-sm">
                        No inspection history recorded yet.
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
