@extends('layouts.verifier')

@section('title', 'Inspection History & Audit Log')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Inspection History Log</h1>
            <p class="text-xs sm:text-sm text-gray-500">Historical archive of certified, graded, and rejected device inspections.</p>
        </div>
        <a href="{{ route('verifier.appointments.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            <span>Back to Active Queue</span>
        </a>
    </div>

    {{-- Filter Toolbar --}}
    <x-card>
        <form method="GET" action="{{ route('verifier.history.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by product, SKU, or seller..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="w-36">
                <select name="status" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Decisions</option>
                    <option value="verified" {{ $currentStatus === 'verified' ? 'selected' : '' }}>Verified / Passed</option>
                    <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>Rejected / Failed</option>
                </select>
            </div>

            <div class="w-32">
                <select name="grade" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Grades</option>
                    <option value="A" {{ $currentGrade === 'A' ? 'selected' : '' }}>Grade A</option>
                    <option value="B" {{ $currentGrade === 'B' ? 'selected' : '' }}>Grade B</option>
                    <option value="C" {{ $currentGrade === 'C' ? 'selected' : '' }}>Grade C</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                Filter
            </button>

            @if ($search || $currentStatus || $currentGrade)
                <a href="{{ route('verifier.history.index') }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-card>
        <x-table :headers="['Product Details', 'Seller', 'Decision', 'Grade / Battery', 'Notes / Rejection', 'Inspected On', 'Action']" id="history-table">
            @forelse ($history as $record)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5 font-medium text-gray-900">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center text-gray-400 border border-gray-100">
                                @if ($record->product?->primaryImageUrl())
                                    <img src="{{ $record->product->primaryImageUrl() }}" alt="{{ $record->product->title }}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                    <div class="hidden w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                        </svg>
                                    </div>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $record->product->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $record->product->category?->name ?? 'Electronics' }}
                                    @if ($record->product->sku)
                                        · SKU: <span class="font-mono text-gray-600">{{ $record->product->sku }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        <p class="font-semibold text-gray-900">{{ $record->seller->name }}</p>
                        <p class="text-gray-400">{{ $record->seller->email }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <x-badge :color="$record->status->badgeColor()">{{ $record->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3.5 text-xs">
                        @if ($record->gradeAssignment)
                            @php
                                $gradeEnum = $record->gradeAssignment->grade instanceof \App\Enums\ProductGrade 
                                    ? $record->gradeAssignment->grade 
                                    : \App\Enums\ProductGrade::tryFrom((string)$record->gradeAssignment->grade);
                                $gradeVal = $gradeEnum?->value ?? (string)$record->gradeAssignment->grade;
                                $gradeStyle = $gradeEnum?->badgeClass() ?? 'bg-gray-100 text-gray-700 border border-gray-200';
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $gradeStyle }}">
                                    Grade {{ $gradeVal }}
                                </span>
                                @if ($record->gradeAssignment->battery_health)
                                    <span class="text-gray-500 font-medium">🔋 {{ $record->gradeAssignment->battery_health }}%</span>
                                @endif
                            </div>
                        @elseif ($record->status->value === 'rejected')
                            <span class="text-red-500 font-medium">Uncertified</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600 max-w-xs">
                        @if ($record->rejection_reason)
                            <p class="text-red-600 font-medium line-clamp-2">“{{ $record->rejection_reason }}”</p>
                        @elseif ($record->notes || $record->gradeAssignment?->grade_notes)
                            <p class="text-gray-600 line-clamp-2">{{ $record->notes ?? $record->gradeAssignment?->grade_notes }}</p>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600 whitespace-nowrap">
                        <p class="font-medium text-gray-900">{{ $record->inspected_at?->format('M j, Y') ?? '—' }}</p>
                        <p class="text-gray-400 text-[11px]">{{ $record->inspected_at?->format('g:i A') }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('chat.start', $record->product) }}" class="inline-block">
                            @csrf
                            <button type="submit" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg border border-gray-200 transition cursor-pointer" title="Message {{ $record->seller->name }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        No historical inspections found matching the filter.
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            <x-pagination :paginator="$history" />
        </div>
    </x-card>
@endsection
