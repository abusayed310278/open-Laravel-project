@extends('layouts.admin')

@section('title', 'Settings — Why Buy')

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
        <h2 class="text-base font-semibold text-gray-900 mb-1">"Why Buy" Section</h2>
        <p class="text-xs text-gray-500 mb-6">Heading and the four feature boxes shown on the homepage. Icons are fixed (shield, truck, leaf, headset).</p>

        <form method="POST" action="{{ route('admin.settings.why-buy.update') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="why-heading">Section heading</label>
                <input id="why-heading" type="text" name="heading" value="{{ old('heading', $content['heading']) }}" maxlength="120" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($content['items'] as $i => $item)
                    <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Box {{ $loop->iteration }}</div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="items[{{ $i }}][title]" value="{{ old("items.$i.title", $item['title']) }}" maxlength="80" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="items[{{ $i }}][text]" value="{{ old("items.$i.text", $item['text']) }}" maxlength="160" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                @endforeach
            </div>

            <x-button type="submit" class="shadow-sm">Save Why Buy Section</x-button>
        </form>
    </div>
@endsection
