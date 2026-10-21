@props(['title' => null, 'action' => null, 'step' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'bg-white border border-gray-100 rounded-md p-5']) }}>
    @if ($title)
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-start gap-3">
                @if ($step)
                    <span class="shrink-0 w-7 h-7 rounded-full border-2 border-brand-500 text-brand-700 flex items-center justify-center text-sm font-bold">{{ $step }}</span>
                @endif
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{!! $title !!}</h2>
                    @if ($description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
                    @endif
                </div>
            </div>
            @if ($action)
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
