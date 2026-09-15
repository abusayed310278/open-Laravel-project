@extends('layouts.admin')

@section('title', 'Settings — Branding')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Logo &amp; Site Icon">
        <form method="POST" action="{{ route('admin.settings.branding.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <x-file-upload name="logo" label="Logo" hint="PNG or SVG, transparent background recommended" />
                    @if ($logo)
                        <div class="flex items-center gap-2 mt-3">
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="Current logo" class="w-10 h-10 rounded border border-gray-100 object-contain bg-white">
                            <span class="text-xs text-gray-400">Current logo</span>
                        </div>
                    @endif
                </div>

                <div>
                    <x-file-upload name="favicon" label="Site icon (favicon)" hint="Square PNG or ICO, at least 32×32" />
                    @if ($favicon)
                        <div class="flex items-center gap-2 mt-3">
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($favicon) }}" alt="Current favicon" class="w-8 h-8 rounded border border-gray-100 object-contain bg-white">
                            <span class="text-xs text-gray-400">Current favicon</span>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="border-gray-100">

            <div class="grid sm:grid-cols-2 gap-6">
                <x-select label="Font" name="brand_font" :options="array_combine($fonts, $fonts)" :selected="$font" />

                <div>
                    <label for="brand_color_primary" class="block text-sm font-medium text-gray-700 mb-1.5">Primary color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="brand_color_primary" id="brand_color_primary" value="{{ $color }}" oninput="document.getElementById('brand_color_hex').value = this.value" class="w-11 h-11 rounded-md border border-gray-200 cursor-pointer">
                        <input type="text" id="brand_color_hex" value="{{ $color }}" oninput="document.getElementById('brand_color_primary').value = this.value" class="flex-1 border border-gray-200 rounded-md px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Used for buttons, links, and badges across the whole site.</p>
                </div>
            </div>

            <x-button type="submit">Save Branding</x-button>
        </form>
    </x-card>
@endsection
