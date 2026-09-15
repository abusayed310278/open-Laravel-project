@props(['headers' => [], 'id' => null])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['id' => $id, 'class' => 'w-full text-sm']) }}>
        @if (count($headers))
            <thead>
                <tr class="border-b border-gray-100">
                    @foreach ($headers as $header)
                        <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3 bg-gray-50">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
