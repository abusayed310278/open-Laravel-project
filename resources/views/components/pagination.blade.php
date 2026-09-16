@props(['paginator'])

@if (isset($paginator) && $paginator->hasPages())
    <div class="mt-4">
        {{ $paginator->links() }}
    </div>
@endif
