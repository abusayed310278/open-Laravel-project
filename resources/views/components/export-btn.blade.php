@props(['tableId', 'filename' => 'export'])

<button
    type="button"
    onclick="exportTable('{{ $tableId }}', '{{ $filename }}')"
    class="flex items-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-md text-xs transition-colors"
>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
    </svg>
    Export CSV
</button>
