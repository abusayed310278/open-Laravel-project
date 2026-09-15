@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex items-center gap-1.5 text-xs text-gray-400']) }}>
    <a href="{{ route('home') }}" class="hover:text-gray-600">Home</a>

    @foreach ($items as $label => $url)
        <span>/</span>
        @if ($url && !$loop->last)
            <a href="{{ $url }}" class="hover:text-gray-600">{{ $label }}</a>
        @else
            <span class="text-gray-600 font-medium">{{ $label }}</span>
        @endif
    @endforeach
</nav>
