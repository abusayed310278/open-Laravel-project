@extends('layouts.admin')

@section('title', 'Settings — Start Selling')

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

    @php
        $field = 'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400';
        $fileField = 'text-xs text-gray-600 w-full file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 hover:file:bg-gray-200';
    @endphp

    <form method="POST" action="{{ route('admin.settings.start-selling.update') }}" enctype="multipart/form-data" class="space-y-6" data-start-selling-form>
        @csrf

        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs space-y-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Banner text &amp; buttons</h2>
                <p class="text-xs text-gray-500 mt-0.5">The "Start selling your electronics" banner on the homepage. Leave a link empty to use the default (seller registration / shop).</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Heading</label>
                    <label class="flex items-center gap-2 text-xs text-gray-500">Font size (px)
                        <input type="number" name="title_size" value="{{ old('title_size', $content['title_size']) }}" min="20" max="120" placeholder="Auto" class="w-20 border border-gray-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </label>
                </div>
                <input type="text" name="title" value="{{ old('title', $content['title']) }}" maxlength="120" required class="{{ $field }}">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <label class="flex items-center gap-2 text-xs text-gray-500">Font size (px)
                        <input type="number" name="text_size" value="{{ old('text_size', $content['text_size']) }}" min="10" max="40" placeholder="Auto" class="w-20 border border-gray-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </label>
                </div>
                <textarea name="text" rows="3" maxlength="300" required class="{{ $field }}">{{ old('text', $content['text']) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Primary button</div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                        <input type="text" name="primary_label" value="{{ old('primary_label', $content['primary_label']) }}" maxlength="40" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link</label>
                        <input type="text" name="primary_url" value="{{ old('primary_url', $content['primary_url']) }}" maxlength="500" placeholder="/register/seller or https://…" class="{{ $field }}">
                    </div>
                </div>
                <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Secondary button</div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                        <input type="text" name="secondary_label" value="{{ old('secondary_label', $content['secondary_label']) }}" maxlength="40" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link</label>
                        <input type="text" name="secondary_url" value="{{ old('secondary_url', $content['secondary_url']) }}" maxlength="500" placeholder="/shop or https://…" class="{{ $field }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs space-y-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Mobile app showcase</h2>
                <p class="text-xs text-gray-500 mt-0.5">The phone mockup and download column. The phone shows only when an image is uploaded; the download column shows when a QR code or store link is set.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">App heading</label>
                    <input type="text" name="app_heading" value="{{ old('app_heading', $content['app_heading']) }}" maxlength="80" required class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">QR caption</label>
                    <input type="text" name="qr_caption" value="{{ old('qr_caption', $content['qr_caption']) }}" maxlength="60" required class="{{ $field }}">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">App text</label>
                <input type="text" name="app_text" value="{{ old('app_text', $content['app_text']) }}" maxlength="160" required class="{{ $field }}">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">App Store (iOS) link</label>
                    <input type="url" name="ios_url" value="{{ old('ios_url', $content['ios_url']) }}" maxlength="500" placeholder="https://apps.apple.com/…" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Play link</label>
                    <input type="url" name="android_url" value="{{ old('android_url', $content['android_url']) }}" maxlength="500" placeholder="https://play.google.com/store/apps/…" class="{{ $field }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Phone image --}}
                <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Mobile picture</div>
                    @if ($content['phone_image'])
                        <img src="{{ $content['phone_image'] }}" alt="Current mobile picture" class="h-40 w-auto object-contain rounded-lg border border-gray-100 bg-gray-50">
                        <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                            <input type="checkbox" name="remove_phone_image" value="1" class="rounded border-gray-300"> Remove current image
                        </label>
                    @endif
                    <input type="file" name="phone_image" accept="image/png,image/jpeg,image/webp" class="{{ $fileField }}">
                    <p class="text-xs text-gray-400">PNG with transparent background works best (portrait, max 3 MB).</p>
                </div>

                {{-- QR code --}}
                <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">QR code</div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Generate QR from link</label>
                        <input type="text" name="qr_url" id="qr_url" value="{{ old('qr_url', $content['qr_url']) }}" maxlength="500" placeholder="https://yourapp.link/download" class="{{ $field }}">
                        <p class="text-xs text-gray-400 mt-1">Type a link and the QR code is created automatically — no upload needed.</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-28 h-28 shrink-0 rounded-lg border border-gray-100 bg-white flex items-center justify-center overflow-hidden">
                            <img id="qr-preview" src="{{ $content['qr_image'] }}" alt="QR preview" class="w-full h-full object-contain {{ $content['qr_image'] ? '' : 'hidden' }}">
                            <span id="qr-empty" class="text-xs text-gray-400 text-center px-2 {{ $content['qr_image'] ? 'hidden' : '' }}">No QR yet</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <label class="block text-gray-600">Or upload your own image</label>
                            <input type="file" name="qr_image" id="qr_image" accept="image/png,image/jpeg,image/webp" class="{{ $fileField }}">
                            @if ($content['qr_image'])
                                <label class="inline-flex items-center gap-2 text-gray-600">
                                    <input type="checkbox" name="remove_qr_image" value="1" class="rounded border-gray-300"> Remove current QR
                                </label>
                            @endif
                        </div>
                    </div>

                    <input type="hidden" name="qr_generated" id="qr_generated" value="">
                </div>
            </div>
        </div>

        <x-button type="submit" class="shadow-sm">Save Start Selling</x-button>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script>
        (() => {
            const urlInput = document.getElementById('qr_url');
            const fileInput = document.getElementById('qr_image');
            const hidden = document.getElementById('qr_generated');
            const preview = document.getElementById('qr-preview');
            const empty = document.getElementById('qr-empty');
            const initialUrl = {{ $content['qr_image'] ? 'urlInput.value.trim()' : "''" }};
            let timer;

            const toPng = (text) => {
                const qr = qrcode(0, 'M');
                qr.addData(text);
                qr.make();
                const count = qr.getModuleCount();
                const cell = 10;
                const margin = 4 * cell;
                const size = count * cell + margin * 2;
                const canvas = document.createElement('canvas');
                canvas.width = canvas.height = size;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, size, size);
                ctx.fillStyle = '#000';
                for (let r = 0; r < count; r++) {
                    for (let c = 0; c < count; c++) {
                        if (qr.isDark(r, c)) {
                            ctx.fillRect(margin + c * cell, margin + r * cell, cell, cell);
                        }
                    }
                }
                return canvas.toDataURL('image/png');
            };

            const refresh = () => {
                const text = urlInput.value.trim();
                if (!text || text === initialUrl || fileInput.files.length) {
                    hidden.value = '';
                    return;
                }
                try {
                    const png = toPng(text);
                    hidden.value = png;
                    preview.src = png;
                    preview.classList.remove('hidden');
                    empty.classList.add('hidden');
                } catch (e) {
                    hidden.value = '';
                }
            };

            urlInput.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(refresh, 300);
            });
            fileInput.addEventListener('change', () => {
                if (fileInput.files[0]) {
                    preview.src = URL.createObjectURL(fileInput.files[0]);
                    preview.classList.remove('hidden');
                    empty.classList.add('hidden');
                }
                refresh();
            });
        })();
    </script>
@endsection
