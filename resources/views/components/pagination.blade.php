@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4 pt-4">
        <p class="text-xs text-gray-400">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="border border-gray-100 text-gray-300 rounded-md px-3 py-1.5 text-sm">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-md px-3 py-1.5 text-sm transition-colors">‹</a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page === $paginator->currentPage())
                    <span class="bg-brand-500 text-white rounded-md px-3 py-1.5 text-sm font-medium">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-md px-3 py-1.5 text-sm transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-md px-3 py-1.5 text-sm transition-colors">›</a>
            @else
                <span class="border border-gray-100 text-gray-300 rounded-md px-3 py-1.5 text-sm">›</span>
            @endif
        </div>
    </nav>
@endif
