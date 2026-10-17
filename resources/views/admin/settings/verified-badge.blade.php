@extends('layouts.admin')

@section('title', 'Settings — Verified Badge')

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

    <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Verified Product Badge</h2>
                <p class="text-xs text-gray-500 mt-0.5">This icon appears on every product card, next to the rating, once a verifier has physically verified the product. Without a custom icon, the default blue check is shown.</p>
            </div>
            @if ($badge)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Custom Icon Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Default Icon
                </span>
            @endif
        </div>

        {{-- Preview on a mini rating row --}}
        <div class="border border-gray-100 rounded-lg p-4 inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" /></svg>
            <span class="text-xs font-semibold text-gray-800">4.9</span>
            <span class="text-xs text-gray-400">(48)</span>
            @if ($badge)
                <img src="{{ $badge }}" alt="Verified badge" class="object-contain" style="width: {{ $badgeSize }}px; height: {{ $badgeSize }}px;">
            @else
                <span class="inline-flex text-blue-500">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.settings.verified-badge.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon image</label>
                <input type="file" name="badge" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="text-sm text-gray-600 w-full file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 hover:file:bg-gray-200">
                <p class="text-xs text-gray-400 mt-1">Square PNG, SVG or WebP with a transparent background works best (max 1 MB). Leave empty to keep the current icon.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon size (px)</label>
                <input type="number" name="size" value="{{ old('size', $badgeSize) }}" min="12" max="64" required class="w-28 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                <p class="text-xs text-gray-400 mt-1">12–64 px, default 24. Applies to your uploaded icon on product cards.</p>
            </div>
            <x-button type="submit" class="shadow-sm">Save</x-button>
        </form>

        @if ($badge)
            <form method="POST" action="{{ route('admin.settings.verified-badge.remove') }}" onsubmit="return confirm('Remove the custom badge and use the default check icon?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 cursor-pointer">Remove custom icon</button>
            </form>
        @endif
    </div>
@endsection
