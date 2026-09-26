@extends('layouts.admin')

@section('title', 'Settings — Footer Icon')

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
        {{-- Footer Icon Preview Card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Dashboard Footer Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time preview of how your dynamic footer icon looks in the dashboard footer.</p>
                </div>
                @if ($footerIcon)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Custom Footer Icon Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Default Icon
                    </span>
                @endif
            </div>

            {{-- Mockup Footer Bar --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 shadow-2xs">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                    <div class="flex items-center gap-3">
                        @php $resolvedFooterIconUrl = \App\Support\MediaUrl::resolve($footerIcon); @endphp
                        <div id="footer-icon-preview" class="h-7 w-auto flex items-center justify-center shrink-0">
                            @if (!empty($resolvedFooterIconUrl))
                                <img src="{{ $resolvedFooterIconUrl }}" alt="Footer Icon" class="h-7 w-auto max-h-7 object-contain">
                            @else
                                <img src="{{ asset('icon.png') }}" alt="Openbox" class="h-7 w-auto max-h-7 object-contain">
                            @endif
                        </div>
                        <span class="font-medium text-gray-700">&copy; {{ date('Y') }} {{ config('app.name', 'Openbox') }}. All rights reserved.</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-400">
                        <span>Admin Dashboard</span>
                        <span>•</span>
                        <span>v1.0.0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Form Card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Upload New Footer Icon</h2>
            <p class="text-xs text-gray-500 mb-6">Upload an icon or small logo image to be displayed dynamically in the dashboard footer.</p>

            <form method="POST" action="{{ route('admin.settings.footericon.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Footer Icon Image</label>
                    <div class="relative border-2 border-dashed border-gray-200 hover:border-brand-400 rounded-xl p-6 text-center transition-colors bg-gray-50/50 hover:bg-white cursor-pointer group">
                        <input
                            type="file"
                            name="footer_icon"
                            id="footer-icon-input"
                            accept="image/png,image/svg+xml,image/jpeg,image/webp,image/x-icon"
                            required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="handleFooterIconChange(event)"
                        >
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-800 mb-1" id="footer-chosen-text">
                                <span class="text-brand-600 font-semibold underline">Click to upload icon</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-400">PNG, SVG, WEBP, or JPG (Recommended height: 28-36px)</p>
                        </div>
                    </div>
                </div>

                {{-- Specs Box --}}
                <div class="rounded-lg bg-amber-50/60 border border-amber-100 p-4 text-xs text-amber-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-semibold">Footer Icon Recommendations:</span>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-amber-700/90">
                            <li>Format: <strong>PNG</strong> or <strong>SVG</strong> with transparent background.</li>
                            <li>Optimal Dimensions: <strong>120 × 32 px</strong> or square <strong>32 × 32 px</strong>.</li>
                            <li>Maximum file size: <strong>2 MB</strong>.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Footer Icon
                    </x-button>

                    @if ($footerIcon)
                        <button
                            type="button"
                            onclick="if(confirm('Are you sure you want to reset to the default footer icon?')) document.getElementById('remove-footericon-form').submit();"
                            class="text-xs font-medium text-red-600 hover:text-red-700 hover:underline px-3 py-2 cursor-pointer"
                        >
                            Reset to Default Icon
                        </button>
                    @endif
                </div>
            </form>

            @if ($footerIcon)
                <form id="remove-footericon-form" method="POST" action="{{ route('admin.settings.footericon.remove') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    <script>
        function handleFooterIconChange(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('footer-chosen-text').innerHTML = `Selected: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const dataUrl = e.target.result;
                    document.getElementById('footer-icon-preview').innerHTML = `<img src="${dataUrl}" class="h-7 w-auto max-h-7 object-contain">`;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
