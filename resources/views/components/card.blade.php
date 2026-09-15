@props(['title' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'bg-white border border-gray-100 rounded-md p-5']) }}>
    @if ($title)
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800">{{ $title }}</h2>
            @if ($action)
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
