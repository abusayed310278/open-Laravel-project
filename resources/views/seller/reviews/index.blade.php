@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Reviews')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        @forelse ($reviews as $review)
            <div class="py-4 border-b border-gray-50 last:border-0">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <x-star-rating :rating="$review->rating" />
                        <span class="text-sm text-gray-700 font-medium">{{ $review->reviewer->name }}</span>
                    </div>
                    <x-badge :color="$review->status->badgeColor()">{{ $review->status->label() }}</x-badge>
                </div>
                @if ($review->title)
                    <p class="font-medium text-gray-900 mt-1">{{ $review->title }}</p>
                @endif
                <p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $review->created_at->format('M j, Y') }}</p>

                @foreach ($review->replies as $reply)
                    <div class="mt-3 ml-4 border-l-2 border-gray-100 pl-3 text-sm text-gray-600">
                        <p class="font-medium text-gray-800 text-xs">Your reply</p>
                        <p>{{ $reply->body }}</p>
                    </div>
                @endforeach

                @if ($review->replies->isEmpty())
                    <button type="button" onclick="document.getElementById('reply-{{ $review->id }}').showModal()" class="text-sm text-brand-600 font-medium hover:underline mt-3">Reply</button>

                    <dialog id="reply-{{ $review->id }}" class="rounded-md p-6 w-full max-w-sm backdrop:bg-black/40">
                        <form method="POST" action="{{ route($routePrefix.'reviews.reply', $review) }}" class="space-y-4">
                            @csrf
                            <x-textarea label="Your reply" name="body" rows="3" required />
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                                <x-button type="submit">Post Reply</x-button>
                            </div>
                        </form>
                    </dialog>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-10">No reviews yet.</p>
        @endforelse

        <x-pagination :paginator="$reviews" />
    </x-card>
@endsection
