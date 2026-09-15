@extends('layouts.admin')

@section('title', 'Settings — Mail')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="SMTP Configuration">
        <form method="POST" action="{{ route('admin.settings.mail.update') }}" class="space-y-5">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="SMTP host" name="mail_host" type="text" :value="old('mail_host', $values['mail_host'])" placeholder="smtp.mailtrap.io" />
                <x-input label="SMTP port" name="mail_port" type="number" :value="old('mail_port', $values['mail_port'])" placeholder="587" />
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <x-select label="Encryption" name="mail_encryption" :options="['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None']" :selected="$values['mail_encryption']" />
                <x-input label="Username" name="mail_username" type="text" :value="old('mail_username', $values['mail_username'])" />
            </div>

            <div>
                <x-input label="Password" name="mail_password" type="password" :placeholder="$hasPassword ? '••••••••  (leave blank to keep current)' : 'Not set'" />
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="From address" name="mail_from_address" type="email" :value="old('mail_from_address', $values['mail_from_address'])" />
                <x-input label="From name" name="mail_from_name" type="text" :value="old('mail_from_name', $values['mail_from_name'])" />
            </div>

            <div class="flex items-center gap-3">
                <x-button type="submit">Save SMTP Settings</x-button>
            </div>
        </form>

        @if ($values['mail_host'])
            <form method="POST" action="{{ route('admin.settings.mail.test') }}" class="mt-3 pt-5 border-t border-gray-100">
                @csrf
                <p class="text-sm text-gray-500 mb-3">Send a test email to your own address to confirm these settings work.</p>
                <x-button type="submit" variant="secondary">Send Test Email</x-button>
            </form>
        @endif
    </x-card>
@endsection
