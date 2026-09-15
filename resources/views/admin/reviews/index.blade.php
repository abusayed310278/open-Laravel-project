@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['Reviewer', 'Type', 'Rating', 'Review', 'Status', 'Date', '']" id="admin-reviews-table">
            @forelse ($reviews as $review)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $review->reviewer->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $review->reviewable_type->label() }} #{{ $review->reviewable_id }}</td>
                    <td class="px-4 py-3"><x-star-rating :rating="$review->rating" /></td>
                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $review->body }}">{{ $review->body }}</td>
                    <td class="px-4 py-3"><x-badge :color="$review->status->badgeColor()">{{ $review->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $review->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        @if ($review->status->value === 'pending')
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 font-medium hover:underline">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No reviews yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$reviews" />
    </x-card>
@endsection
