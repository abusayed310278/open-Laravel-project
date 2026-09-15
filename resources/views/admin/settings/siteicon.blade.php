@extends('layouts.admin')

@section('title', 'Settings — Site Icon')

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
        {{-- Realistic Browser Tab Mockup --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Browser Tab Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time simulation of how your site icon looks in a browser tab bar.</p>
                </div>
                @if ($favicon)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Custom Favicon Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Default Favicon
                    </span>
                @endif
            </div>

            {{-- Mockup Window Chrome --}}
            <div class="bg-gray-100/90 border border-gray-200 rounded-xl overflow-hidden shadow-xs">
                {{-- Top Tab Bar --}}
                <div class="flex items-center gap-2 px-3 pt-2.5 bg-gray-200/80 border-b border-gray-300/60">
                    <div class="flex items-center gap-1.5 mr-3">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                    </div>

                    {{-- Active Tab --}}
                    <div class="bg-white px-4 py-2 rounded-t-lg border-t border-x border-gray-200 shadow-xs flex items-center gap-2.5 min-w-[200px] max-w-[260px]">
                        <div id="mock-tab-icon" class="w-4 h-4 flex items-center justify-center shrink-0">
                            @if ($favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($favicon))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($favicon) }}" alt="Site Icon" class="w-4 h-4 object-contain">
                            @else
                                <img src="{{ asset('icon.png') }}" alt="Openbox" class="w-4 h-4 object-contain">
                            @endif
                        </div>
                        <span class="text-xs font-medium text-gray-800 truncate">Openbox — Modern Marketplace</span>
                        <span class="text-gray-400 text-xs ml-auto hover:text-gray-600 cursor-pointer">×</span>
                    </div>

                    <div class="text-gray-400 px-2 text-sm">+</div>
                </div>

                {{-- URL Address Bar --}}
                <div class="bg-white px-4 py-2.5 border-b border-gray-100 flex items-center gap-3 text-xs text-gray-500">
                    <div class="flex items-center gap-2 text-gray-400">
                        <span>←</span>
                        <span>→</span>
                        <span>↻</span>
                    </div>
                    <div class="flex-1 bg-gray-50 border border-gray-200 rounded-md px-3 py-1 flex items-center gap-2 font-mono text-xs text-gray-600">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>https://openbox.test</span>
                    </div>
                </div>
            </div>

            {{-- Multi-Resolution Previews --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Resolution Display Matrix</div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="border border-gray-100 rounded-lg p-3 text-center bg-gray-50/50">
                        <div class="h-14 flex items-center justify-center">
                            <div class="w-4 h-4 preview-box flex items-center justify-center">
                                @if ($favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($favicon))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($favicon) }}" class="w-4 h-4 object-contain">
                                @else
                                    <img src="{{ asset('icon.png') }}" class="w-4 h-4 object-contain">
                                @endif
                            </div>
                        </div>
                        <div class="text-[11px] font-semibold text-gray-700 mt-2">16 × 16 px</div>
                        <div class="text-[10px] text-gray-400">Standard Tab</div>
                    </div>

                    <div class="border border-gray-100 rounded-lg p-3 text-center bg-gray-50/50">
                        <div class="h-14 flex items-center justify-center">
                            <div class="w-8 h-8 preview-box flex items-center justify-center">
                                @if ($favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($favicon))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($favicon) }}" class="w-8 h-8 object-contain">
                                @else
                                    <img src="{{ asset('icon.png') }}" class="w-8 h-8 object-contain">
                                @endif
                            </div>
                        </div>
                        <div class="text-[11px] font-semibold text-gray-700 mt-2">32 × 32 px</div>
                        <div class="text-[10px] text-gray-400">Retina Tab</div>
                    </div>

                    <div class="border border-gray-100 rounded-lg p-3 text-center bg-gray-50/50">
                        <div class="h-14 flex items-center justify-center">
                            <div class="w-12 h-12 preview-box flex items-center justify-center">
                                @if ($favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($favicon))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($favicon) }}" class="w-12 h-12 object-contain">
                                @else
                                    <img src="{{ asset('icon.png') }}" class="w-12 h-12 object-contain">
                                @endif
                            </div>
                        </div>
                        <div class="text-[11px] font-semibold text-gray-700 mt-2">48 × 48 px</div>
                        <div class="text-[10px] text-gray-400">Desktop Icon</div>
                    </div>

                    <div class="border border-gray-100 rounded-lg p-3 text-center bg-gray-50/50">
                        <div class="h-14 flex items-center justify-center">
                            <div class="w-14 h-14 preview-box flex items-center justify-center">
                                @if ($favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($favicon))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($favicon) }}" class="w-14 h-14 object-contain">
                                @else
                                    <img src="{{ asset('icon.png') }}" class="w-14 h-14 object-contain">
                                @endif
                            </div>
                        </div>
                        <div class="text-[11px] font-semibold text-gray-700 mt-2">96 × 96 px</div>
                        <div class="text-[10px] text-gray-400">Touch Icon / Mobile</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Form --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Upload New Site Icon (Favicon)</h2>
            <p class="text-xs text-gray-500 mb-6">Upload a crisp square icon used as the browser bookmark, tab icon, and mobile shortcut.</p>

            <form method="POST" action="{{ route('admin.settings.siteicon.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Icon File</label>
                    <div class="relative border-2 border-dashed border-gray-200 hover:border-brand-400 rounded-xl p-6 text-center transition-colors bg-gray-50/50 hover:bg-white cursor-pointer group">
                        <input
                            type="file"
                            name="favicon"
                            id="favicon-input"
                            accept=".ico,image/png,image/svg+xml,image/jpeg,image/webp"
                            required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="handleFaviconChange(event)"
                        >
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-800 mb-1" id="favicon-chosen-text">
                                <span class="text-brand-600 font-semibold underline">Click to upload icon</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-400">Square ICO, PNG, or SVG (Recommended: 32×32 or 64×64)</p>
                        </div>
                    </div>
                </div>

                {{-- Dimension Guidelines --}}
                <div class="rounded-lg bg-amber-50/60 border border-amber-100 p-4 text-xs text-amber-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <span class="font-semibold">Recommended Icon Specifications:</span>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-amber-700/90">
                            <li>Aspect Ratio: Exact <strong>1:1 Square</strong> for distortion-free scaling.</li>
                            <li>Format: <strong>PNG</strong> or <strong>ICO</strong> with transparent background.</li>
                            <li>Optimal resolution: <strong>64 × 64 px</strong> or <strong>128 × 128 px</strong>.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Site Icon
                    </x-button>

                    @if ($favicon)
                        <button
                            type="button"
                            onclick="if(confirm('Are you sure you want to reset to the default favicon?')) document.getElementById('remove-favicon-form').submit();"
                            class="text-xs font-medium text-red-600 hover:text-red-700 hover:underline px-3 py-2"
                        >
                            Reset to Default Favicon
                        </button>
                    @endif
                </div>
            </form>

            @if ($favicon)
                <form id="remove-favicon-form" method="POST" action="{{ route('admin.settings.siteicon.remove') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    <script>
        function handleFaviconChange(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('favicon-chosen-text').innerHTML = `Selected: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const dataUrl = e.target.result;
                    document.getElementById('mock-tab-icon').innerHTML = `<img src="${dataUrl}" class="w-4 h-4 object-contain">`;
                    
                    document.querySelectorAll('.preview-box').forEach(box => {
                        box.innerHTML = `<img src="${dataUrl}" class="w-full h-full object-contain">`;
                    });
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
