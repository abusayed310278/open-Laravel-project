@props(['question'])

<div class="border-b border-gray-100 faq-item">
    <button type="button" class="faq-btn w-full flex items-center justify-between gap-4 py-4 text-left">
        <span class="text-sm font-medium text-gray-800">{{ $question }}</span>
        <span class="faq-icon text-gray-400 text-lg leading-none flex-shrink-0">+</span>
    </button>
    <div class="hidden pb-4 text-sm text-gray-500 leading-relaxed">
        {{ $slot }}
    </div>
</div>
