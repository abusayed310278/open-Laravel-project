@extends('layouts.admin')

@section('title', 'Settings — Storage')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="space-y-6">

        {{-- Form 1: Active Storage Driver Selection --}}
        <x-card title="Active Storage Driver">
            <form method="POST" action="{{ route('admin.settings.storage.update-active') }}" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-select
                        label="Active storage disk"
                        name="storage_disk"
                        :options="[
                            'public' => 'Local (public disk)',
                            'r2' => 'Cloudflare R2',
                            'cloudinary' => 'Cloudinary',
                        ]"
                        :selected="old('storage_disk', $values['storage_disk'])"
                    />
                    <x-input
                        label="Local storage CDN domain (optional)"
                        name="storage_cdn_url"
                        type="text"
                        :value="old('storage_cdn_url', $values['storage_cdn_url'] ?? '')"
                        placeholder="https://cdn.your-domain.com"
                        hint="Optional custom CDN URL for serving Local Storage media (e.g. CloudFront, BunnyCDN, Cloudflare Proxy)"
                    />
                </div>
                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit">Save Active Driver & CDN</x-button>
                </div>
                <p class="text-xs text-gray-400">Switches where new uploads (logos, banners, photos) are stored. Existing files automatically resolve via the active driver or custom CDN.</p>
            </form>
        </x-card>

        {{-- Form 2: Cloudinary Storage Configuration --}}
        <x-card title="Cloudinary Storage Configuration">
            <form method="POST" action="{{ route('admin.settings.storage.update-cloudinary') }}" class="space-y-5">
                @csrf

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="Cloud name" name="cloudinary_cloud_name" type="text" :value="old('cloudinary_cloud_name', $values['cloudinary_cloud_name'])" placeholder="w36cggya" />
                    <x-input label="API key" name="cloudinary_api_key" type="text" :value="old('cloudinary_api_key', $values['cloudinary_api_key'])" placeholder="146238954648692" />
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="API secret" name="cloudinary_api_secret" type="password" :placeholder="$hasCloudinarySecret ? '••••••••  (leave blank to keep current)' : 'Not set'" />
                    <x-input label="API environment variable (CLOUDINARY_URL)" name="cloudinary_url" type="text" :value="old('cloudinary_url', $values['cloudinary_url'])" placeholder="cloudinary://146238954648692:BEuXx-vqfq-tPmWMTaRbJd6SyH8@w36cggya" hint="Pasting CLOUDINARY_URL auto-populates Cloud Name, API Key, and Secret" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit">Save Cloudinary Settings</x-button>
                </div>
            </form>
        </x-card>

        {{-- Form 3: Cloudflare R2 Configuration --}}
        <x-card title="Cloudflare R2 Storage Configuration">
            <form method="POST" action="{{ route('admin.settings.storage.update-r2') }}" class="space-y-5">
                @csrf

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="Access Key ID" name="r2_access_key_id" type="text" :value="old('r2_access_key_id', $values['r2_access_key_id'])" />
                    <x-input label="Secret Access Key" name="r2_secret_access_key" type="password" :placeholder="$hasR2Secret ? '••••••••  (leave blank to keep current)' : 'Not set'" />
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="Bucket" name="r2_bucket" type="text" :value="old('r2_bucket', $values['r2_bucket'])" />
                    <x-input label="Region" name="r2_region" type="text" :value="old('r2_region', $values['r2_region'])" placeholder="auto" />
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="Endpoint" name="r2_endpoint" type="text" :value="old('r2_endpoint', $values['r2_endpoint'])" placeholder="https://<account-id>.r2.cloudflarestorage.com" />
                    <x-input label="Public URL (optional)" name="r2_url" type="text" :value="old('r2_url', $values['r2_url'])" placeholder="https://cdn.your-domain.com" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit">Save Cloudflare R2 Settings</x-button>
                </div>
            </form>
        </x-card>

        {{-- Connection Test Cards with AJAX (No Full Page Reload) --}}
        <div class="grid sm:grid-cols-2 gap-5">
            @if ($values['cloudinary_cloud_name'])
                <x-card title="Test Cloudinary Connection">
                    <p class="text-xs text-gray-500 mb-3">Upload and delete a tiny test file to verify Cloudinary credentials.</p>
                    <form method="POST" action="{{ route('admin.settings.storage.test') }}" class="js-test-storage-form">
                        @csrf
                        <input type="hidden" name="disk" value="cloudinary">
                        <button type="submit" class="js-test-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition border border-gray-300 cursor-pointer">
                            <span class="js-btn-icon">⚡</span>
                            <span class="js-btn-label">Test Cloudinary Connection</span>
                        </button>
                    </form>
                    <div class="js-test-result-box mt-3 hidden"></div>
                </x-card>
            @endif

            @if ($values['r2_access_key_id'])
                <x-card title="Test Cloudflare R2 Connection">
                    <p class="text-xs text-gray-500 mb-3">Upload and delete a tiny test file to verify Cloudflare R2 credentials.</p>
                    <form method="POST" action="{{ route('admin.settings.storage.test') }}" class="js-test-storage-form">
                        @csrf
                        <input type="hidden" name="disk" value="r2">
                        <button type="submit" class="js-test-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition border border-gray-300 cursor-pointer">
                            <span class="js-btn-icon">⚡</span>
                            <span class="js-btn-label">Test R2 Connection</span>
                        </button>
                    </form>
                    <div class="js-test-result-box mt-3 hidden"></div>
                </x-card>
            @endif
        </div>

        {{-- History Log Card --}}
        <x-card title="Storage Settings Change History" id="storage-history">
            <x-slot:action>
                @if (isset($historyLogs) && $historyLogs->total() > 0)
                    <button type="button" onclick="toggleClearHistoryModal(true)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span>Clear History</span>
                    </button>
                @endif
            </x-slot:action>

            @if (isset($historyLogs) && $historyLogs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3">Date & Time</th>
                                <th class="px-4 py-3">Admin User</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Details / Properties</th>
                                <th class="px-4 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-sans">
                            @foreach ($historyLogs as $log)
                                @php
                                    $actionLabel = match($log->action) {
                                        'settings.storage.cloudinary_updated' => 'Cloudinary Updated',
                                        'settings.storage.r2_updated' => 'Cloudflare R2 Updated',
                                        'settings.storage.active_disk_changed' => 'Active Disk Changed',
                                        'settings.storage.tested' => 'Connection Tested',
                                        'settings.storage.history_cleared' => 'History Cleared',
                                        default => 'Storage Updated',
                                    };
                                    $badgeColor = match($log->action) {
                                        'settings.storage.cloudinary_updated' => 'sky',
                                        'settings.storage.r2_updated' => 'amber',
                                        'settings.storage.active_disk_changed' => 'emerald',
                                        'settings.storage.tested' => 'indigo',
                                        'settings.storage.history_cleared' => 'red',
                                        default => 'gray',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('M d, Y · H:i:s') }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $log->user->name ?? 'System Admin' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <x-badge :color="$badgeColor">{{ $actionLabel }}</x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 font-mono">
                                        @if (!empty($log->properties))
                                            @if (isset($log->properties['to_disk']))
                                                Switched disk: <strong class="uppercase text-brand-600">{{ $log->properties['to_disk'] }}</strong>
                                            @elseif (isset($log->properties['cloud_name']))
                                                Cloud: {{ $log->properties['cloud_name'] }}
                                            @elseif (isset($log->properties['bucket']))
                                                Bucket: {{ $log->properties['bucket'] }}
                                            @elseif (isset($log->properties['disk']))
                                                Tested disk: <strong class="uppercase text-indigo-600">{{ $log->properties['disk'] }}</strong>
                                            @else
                                                {{ json_encode($log->properties) }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-400 font-mono whitespace-nowrap">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($historyLogs->hasPages())
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        {{ $historyLogs->links() }}
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-400 italic">No storage configuration changes logged yet.</p>
            @endif
        </x-card>

    </div>

    {{-- AJAX Script for Connection Testing without Full Page Reload --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.js-test-storage-form');

            forms.forEach(function (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const btn = form.querySelector('.js-test-btn');
                    const btnIcon = form.querySelector('.js-btn-icon');
                    const btnLabel = form.querySelector('.js-btn-label');
                    const resultBox = form.parentElement.querySelector('.js-test-result-box');

                    if (!btn || !resultBox) return;

                    const originalLabel = btnLabel ? btnLabel.textContent : 'Test Connection';

                    // Set loading state
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    if (btnIcon) btnIcon.innerHTML = '<svg class="animate-spin w-3.5 h-3.5 text-gray-600 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    if (btnLabel) btnLabel.textContent = 'Testing connection...';

                    resultBox.classList.add('hidden');
                    resultBox.innerHTML = '';

                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        resultBox.classList.remove('hidden');

                        if (response.ok && data.success) {
                            resultBox.innerHTML = `
                                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>${data.message}</span>
                                </div>
                            `;
                        } else {
                            const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Connection test failed.');
                            resultBox.innerHTML = `
                                <div class="p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>${errorMsg}</span>
                                </div>
                            `;
                        }
                    } catch (err) {
                        resultBox.classList.remove('hidden');
                        resultBox.innerHTML = `
                            <div class="p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-xs font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Network or server error while testing connection.</span>
                            </div>
                        `;
                    } finally {
                        btn.disabled = false;
                        btn.classList.remove('opacity-75', 'cursor-not-allowed');
                        if (btnIcon) btnIcon.textContent = '⚡';
                        if (btnLabel) btnLabel.textContent = originalLabel;
                    }
                });
            });
        });

        function toggleClearHistoryModal(show) {
            const modal = document.getElementById('clear-history-modal');
            if (!modal) return;
            if (show) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    </script>

    {{-- Centered Modal Overlay for Clearing History --}}
    <div id="clear-history-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4 overflow-y-auto" onclick="if(event.target === this) toggleClearHistoryModal(false)">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center transform transition-all border border-gray-100 relative">
            <button type="button" onclick="toggleClearHistoryModal(false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer" aria-label="Close modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="w-14 h-14 rounded-full bg-red-50 text-red-500 border border-red-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <h3 class="text-lg font-black text-gray-950">Clear Storage Change History?</h3>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                Are you sure you want to delete all storage change history logs? This action will permanently remove all logged change entries.
            </p>

            <div class="flex items-center justify-center gap-3 mt-6">
                <button type="button" onclick="toggleClearHistoryModal(false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <form method="POST" action="{{ route('admin.settings.storage.clear-history') }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Clear All History
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
