@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">My Reviews</h1>

        <x-card>
            @forelse ($reviews as $review)
                <div class="py-4 border-b border-gray-50 last:border-0">
                    <div class="flex items-center justify-between mb-1">
                        <x-star-rating :rating="$review->rating" />
                        <x-badge :color="$review->status->badgeColor()">{{ $review->status->label() }}</x-badge>
                    </div>
                    @if ($review->title)
                        <p class="font-medium text-gray-900 mt-1">{{ $review->title }}</p>
                    @endif
                    <p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ $review->created_at->format('M j, Y') }} · Order {{ $review->order->order_number }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-10">You haven't written any reviews yet.</p>
            @endforelse

            <x-pagination :paginator="$reviews" />
        </x-card>
    </div>
@endsection
