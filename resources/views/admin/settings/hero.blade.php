@extends('layouts.admin')

@section('title', 'Settings — Hero')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @if (isset($errors) && $errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="space-y-6">
        {{-- Hero Image --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Homepage Hero Image</h2>
            <p class="text-xs text-gray-500 mb-4">Full-width image at the top of the homepage. When set, it replaces the hero text below. Recommended wide landscape (~3.5:1), e.g. 1920 × 550 px.</p>

            @php $resolvedHeroUrl = $heroImage ? \App\Support\MediaUrl::resolve($heroImage) : null; @endphp

            @if ($resolvedHeroUrl)
                <div class="border border-gray-200 rounded-xl overflow-hidden mb-4">
                    <img src="{{ $resolvedHeroUrl }}" alt="Homepage hero" class="w-full h-auto">
                </div>
            @else
                <div class="border border-dashed border-gray-200 rounded-xl p-6 mb-4 text-center text-xs text-gray-400">
                    No hero image uploaded — the hero text below is shown on the homepage.
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.hero-image.update') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <input type="file" name="hero_image" accept="image/png,image/jpeg,image/webp" required class="text-sm text-gray-600 flex-1">
                <x-button type="submit" class="shadow-sm">Save Hero Image</x-button>
            </form>

            @if ($resolvedHeroUrl)
                <form method="POST" action="{{ route('admin.settings.hero-image.remove') }}" class="mt-3" onsubmit="return confirm('Remove the homepage hero image?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 hover:underline">Remove hero image</button>
                </form>
            @endif
        </div>

        {{-- Hero Text --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Hero Text</h2>
            <p class="text-xs text-gray-500 mb-4">Shown on the homepage when no hero image is uploaded.</p>

            <form method="POST" action="{{ route('admin.settings.hero-text.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-badge">Top badge</label>
                    <input id="hero-badge" type="text" name="badge" value="{{ old('badge', $hero['badge']) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to hide the badge.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-title">Headline (line 1)</label>
                        <input id="hero-title" type="text" name="title" value="{{ old('title', $hero['title']) }}" maxlength="120" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-highlight">Headline (highlighted line 2)</label>
                        <input id="hero-highlight" type="text" name="highlight" value="{{ old('highlight', $hero['highlight']) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-subtitle">Subtext</label>
                    <textarea id="hero-subtitle" name="subtitle" rows="3" maxlength="500" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">{{ old('subtitle', $hero['subtitle']) }}</textarea>
                </div>

                <x-button type="submit" class="shadow-sm">Save Hero Text</x-button>
            </form>
        </div>
    </div>
@endsection
