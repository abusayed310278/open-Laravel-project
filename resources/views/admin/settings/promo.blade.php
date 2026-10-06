@extends('layouts.admin')

@section('title', 'Settings — Promo Cards')

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
        $cardLabels = [1 => 'Card 1 (brown)', 2 => 'Card 2 (light)', 3 => 'Card 3 (dark)'];
        $autoHints = [1 => 'newest live product in Laptops', 2 => 'newest live product in Smartphones', 3 => 'newest live product with a photo'];
    @endphp

    <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
        <h2 class="text-base font-semibold text-gray-900 mb-1">Homepage Promo Cards</h2>
        <p class="text-xs text-gray-500 mb-6">The three cards under "Why Buy". Each card shows a real product photo and opens that product's page. Use a new line in the text boxes to break lines.</p>

        <form method="POST" action="{{ route('admin.settings.promo.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach ($cards as $n => $card)
                    <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $cardLabels[$n] }}</div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <textarea name="cards[{{ $n }}][title]" rows="2" maxlength="120" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">{{ old("cards.$n.title", $card['title']) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $n === 2 ? 'Subtitle' : 'Description' }}</label>
                            <textarea name="cards[{{ $n }}][text]" rows="2" maxlength="200" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">{{ old("cards.$n.text", $card['text']) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Button label</label>
                            <input type="text" name="cards[{{ $n }}][button]" value="{{ old("cards.$n.button", $card['button']) }}" maxlength="40" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                            <select name="cards[{{ $n }}][product_id]" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option value="">Auto — {{ $autoHints[$n] }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected((string) old("cards.$n.product_id", $card['product_id']) === (string) $product->id)>{{ $product->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-button type="submit" class="shadow-sm">Save Promo Cards</x-button>
        </form>
    </div>
@endsection
