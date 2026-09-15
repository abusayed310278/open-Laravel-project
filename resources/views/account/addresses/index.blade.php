@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Saved Addresses</h1>
            <button type="button" onclick="document.getElementById('add-address-form').classList.toggle('hidden')" class="inline-flex items-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2.5">
                Add Address
            </button>
        </div>

        <x-card id="add-address-form" class="hidden">
            <form method="POST" action="{{ route('account.addresses.store') }}" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input label="Label" name="label" placeholder="Home, Office..." value="{{ old('label') }}" />
                    <x-input label="Full Name" name="name" value="{{ old('name') }}" />
                    <x-input label="Phone" name="phone" value="{{ old('phone') }}" />
                    <x-input label="City" name="city" value="{{ old('city') }}" />
                    <x-input label="Address Line 1" name="line1" value="{{ old('line1') }}" class="sm:col-span-2" />
                    <x-input label="Address Line 2 (optional)" name="line2" value="{{ old('line2') }}" class="sm:col-span-2" />
                    <x-input label="State / Province (optional)" name="state" value="{{ old('state') }}" />
                    <x-input label="Country" name="country" value="{{ old('country') }}" />
                    <x-input label="Postal Code (optional)" name="postal_code" value="{{ old('postal_code') }}" />
                </div>
                <x-checkbox name="is_default">Set as default address</x-checkbox>
                <x-button type="submit">Save Address</x-button>
            </form>
        </x-card>

        @if ($addresses->isEmpty())
            <x-card>
                <p class="text-sm text-gray-400 text-center py-6">No saved addresses yet.</p>
            </x-card>
        @else
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($addresses as $address)
                    <div @class([
                        'border rounded-xl p-4',
                        'border-brand-300 bg-brand-50/40' => $address->is_default,
                        'border-gray-100' => ! $address->is_default,
                    ])>
                        <div class="flex items-start justify-between">
                            <div>
                                @if ($address->is_default)
                                    <span class="text-xs font-semibold text-brand-600 uppercase tracking-wide">Default</span>
                                @endif
                                <p class="font-semibold text-gray-900">{{ $address->label }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('account.addresses.set-default', $address) }}">
                                        @csrf
                                        <button type="submit" class="text-brand-600 hover:underline">Set Default</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Remove</button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ $address->name }}</p>
                        <p class="text-sm text-gray-500">{{ $address->oneLine() }}</p>
                        <p class="text-sm text-gray-500">{{ $address->phone }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
