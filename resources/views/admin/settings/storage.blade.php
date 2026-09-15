@extends('layouts.admin')

@section('title', 'Settings — Storage')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Cloudflare R2">
        <form method="POST" action="{{ route('admin.settings.storage.update') }}" class="space-y-5">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="Access key ID" name="r2_access_key_id" type="text" :value="old('r2_access_key_id', $values['r2_access_key_id'])" />
                <x-input label="Secret access key" name="r2_secret_access_key" type="password" :placeholder="$hasSecret ? '••••••••  (leave blank to keep current)' : 'Not set'" />
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="Bucket" name="r2_bucket" type="text" :value="old('r2_bucket', $values['r2_bucket'])" />
                <x-input label="Region" name="r2_region" type="text" :value="old('r2_region', $values['r2_region'])" placeholder="auto" />
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="Endpoint" name="r2_endpoint" type="text" :value="old('r2_endpoint', $values['r2_endpoint'])" placeholder="https://<account-id>.r2.cloudflarestorage.com" />
                <x-input label="Public URL (optional)" name="r2_url" type="text" :value="old('r2_url', $values['r2_url'])" placeholder="https://cdn.your-domain.com" />
            </div>

            <x-select
                label="Active disk"
                name="storage_disk"
                :options="['public' => 'Local (public disk)', 'r2' => 'Cloudflare R2']"
                :selected="$values['storage_disk']"
            />
            <p class="text-xs text-gray-400 -mt-3">Switches where new uploads are stored. Existing files are not moved automatically.</p>

            <x-button type="submit">Save Storage Settings</x-button>
        </form>

        @if ($values['r2_access_key_id'])
            <form method="POST" action="{{ route('admin.settings.storage.test') }}" class="mt-3 pt-5 border-t border-gray-100">
                @csrf
                <p class="text-sm text-gray-500 mb-3">Write and delete a small test file to confirm these credentials work.</p>
                <x-button type="submit" variant="secondary">Test Connection</x-button>
            </form>
        @endif
    </x-card>
@endsection
