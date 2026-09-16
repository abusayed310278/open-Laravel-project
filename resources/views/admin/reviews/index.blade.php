@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Customer & Seller Reviews</x-slot:title>
        <x-slot:action>
            <form method="GET" class="flex items-center">
                <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white text-gray-700 hover:border-gray-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 cursor-pointer">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:action>

        <x-table :headers="['Reviewer', 'Target', 'Rating', 'Review Details', 'Status', 'Date', 'Actions']" id="admin-reviews-table">
            @forelse ($reviews as $review)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0 border border-brand-100">
                                {{ strtoupper(substr($review->reviewer?->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-900 block">{{ $review->reviewer?->name ?? 'Deleted User' }}</span>
                                @if ($review->reviewer?->email)
                                    <span class="text-xs text-gray-400">{{ $review->reviewer->email }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        <span class="font-semibold text-gray-800">{{ $review->reviewable_type->label() }}</span>
                        <span class="text-gray-400 font-mono">#{{ $review->reviewable_id }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 font-mono">
                        {{ $review->rating }}/5
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 max-w-xs" title="{{ $review->body }}">
                        <span class="text-gray-700">{{ \Illuminate\Support\Str::words($review->body, 5, '...') }}</span>
                        @if ($review->images && $review->images->count() > 0)
                            <span class="inline-flex items-center gap-0.5 text-[10px] text-brand-600 ml-1 font-medium" title="{{ $review->images->count() }} attached photo(s)">
                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $review->images->count() }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$review->status->badgeColor()">{{ $review->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $review->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- View Icon --}}
                            <button type="button" data-modal-open="view-review-modal-{{ $review->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="View review details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>

                            {{-- Approve Icon (if not approved) --}}
                            @if ($review->status->value !== 'approved')
                                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline-block m-0">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Approve review">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </button>
                                </form>
                            @endif

                            {{-- Reject Icon (if not rejected) --}}
                            @if ($review->status->value !== 'rejected')
                                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline-block m-0">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Reject review">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                            @endif

                            {{-- Edit Icon --}}
                            <button type="button" data-modal-open="edit-review-modal-{{ $review->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit review">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm="Delete this review permanently?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete review">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No reviews found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$reviews" />
    </x-card>

    {{-- View Review Modals --}}
    @foreach ($reviews as $review)
        <x-modal id="view-review-modal-{{ $review->id }}" title="Review Details" maxWidth="max-w-lg">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($review->reviewer?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $review->reviewer?->name ?? 'Anonymous User' }}</div>
                            <div class="text-xs text-gray-400">{{ $review->reviewer?->email }}</div>
                        </div>
                    </div>
                    <div>
                        <x-badge :color="$review->status->badgeColor()">{{ $review->status->label() }}</x-badge>
                    </div>
                </div>

                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-lg">
                    <div class="flex items-center gap-1.5">
                        <x-star-rating :rating="$review->rating" />
                        <span class="text-xs font-bold text-gray-900">({{ $review->rating }} out of 5)</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Target: <span class="font-semibold text-gray-800">{{ $review->reviewable_type->label() }} #{{ $review->reviewable_id }}</span>
                    </div>
                </div>

                @if ($review->title)
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Title</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $review->title }}</div>
                    </div>
                @endif

                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Review Body</div>
                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg leading-relaxed whitespace-pre-line">{{ $review->body }}</p>
                </div>

                @if ($review->images && $review->images->count() > 0)
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Attached Photos</div>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($review->images as $image)
                                <img src="{{ $image->url }}" alt="Review photo" class="w-full h-24 object-cover rounded-lg border border-gray-100">
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="text-xs text-gray-400 pt-2">
                    Submitted on {{ $review->created_at->format('F j, Y \a\t g:i A') }}
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Close
                    </button>
                    <button type="button" data-modal-close data-modal-open="edit-review-modal-{{ $review->id }}" class="whitespace-nowrap px-4 py-2 text-xs font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition cursor-pointer">
                        Edit Review
                    </button>
                </div>
            </div>
        </x-modal>

        {{-- Edit Review Modal --}}
        <x-modal id="edit-review-modal-{{ $review->id }}" title="Edit Review" maxWidth="max-w-lg">
            <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Rating (Stars)</label>
                        <select name="rating" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('rating', $review->rating) == $i)>{{ $i }} Stars ({{ str_repeat('★', $i) }})</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                            @foreach ($statuses as $st)
                                <option value="{{ $st->value }}" @selected(old('status', $review->status->value) === $st->value)>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <x-input label="Review Title (Optional)" name="title" type="text" :value="old('title', $review->title)" placeholder="Summary headline" />

                <x-textarea label="Review Body" name="body" rows="4" required>{{ old('body', $review->body) }}</x-textarea>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Save Changes</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
