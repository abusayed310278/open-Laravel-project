@props(['id', 'title' => null, 'maxWidth' => 'max-w-lg'])

<div id="{{ $id }}" data-modal class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-md w-full {{ $maxWidth }} shadow-xl max-h-[90vh] overflow-y-auto">
        @if ($title)
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
        @endif

        <div class="px-6 py-5">{{ $slot }}</div>
    </div>
</div>
