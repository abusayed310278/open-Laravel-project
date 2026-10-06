@extends('layouts.admin')

@section('title', 'Settings — Newsletter')

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

    <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
        <h2 class="text-base font-semibold text-gray-900 mb-1">"Stay Updated" Banner</h2>
        <p class="text-xs text-gray-500 mb-6">The yellow newsletter banner at the bottom of the homepage.</p>

        <form method="POST" action="{{ route('admin.settings.newsletter.update') }}" class="space-y-4 max-w-xl">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="nl-title">Heading</label>
                <input id="nl-title" type="text" name="title" value="{{ old('title', $content['title']) }}" maxlength="80" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="nl-text">Subtext</label>
                <input id="nl-text" type="text" name="text" value="{{ old('text', $content['text']) }}" maxlength="160" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="nl-placeholder">Email field placeholder</label>
                    <input id="nl-placeholder" type="text" name="placeholder" value="{{ old('placeholder', $content['placeholder']) }}" maxlength="60" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="nl-button">Button label</label>
                    <input id="nl-button" type="text" name="button" value="{{ old('button', $content['button']) }}" maxlength="30" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <x-button type="submit" class="shadow-sm">Save Newsletter Banner</x-button>
        </form>
    </div>
@endsection
