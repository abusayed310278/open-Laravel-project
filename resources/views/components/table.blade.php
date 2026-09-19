@props(['headers' => [], 'id' => null, 'containerClass' => ''])

<div class="overflow-x-auto overflow-y-hidden pb-1 [&::-webkit-scrollbar]:h-2.5 [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-track]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400 [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-gray-500 [scrollbar-width:auto] [scrollbar-color:#94a3b8_#f1f5f9] {{ $containerClass }}">
    <table {{ $attributes->merge(['id' => $id, 'class' => 'w-full text-sm']) }}>
        @if (count($headers))
            <thead>
                <tr class="border-b border-gray-100">
                    @foreach ($headers as $header)
                        <th class="{{ in_array(strtolower(trim(strip_tags($header))), ['actions', 'action']) ? 'text-right' : 'text-left' }} text-xs font-semibold text-gray-500 px-4 py-3 bg-gray-50">{!! $header !!}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
