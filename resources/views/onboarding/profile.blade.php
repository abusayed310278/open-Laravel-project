@php($maxWidth = 'max-w-2xl')
@extends('layouts.auth')

@section('title', 'Finish setting up your store')

@section('content')
    @php($isBusiness = auth()->user()->role === \App\Enums\UserRole::Business)

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Finish setting up your store</h1>
    <p class="text-sm text-gray-500 mb-6">
        Complete your store branding, location details, and KYC verification documents to activate your {{ $isBusiness ? 'Store Owner' : 'Seller' }} account.
    </p>

    <form method="POST" action="{{ route('onboarding.profile.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Store Branding: Logo & Cover Photo --}}
        <div class="bg-gray-50/50 p-4 border border-gray-100 rounded-xl space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Store Branding</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-file-upload name="logo" label="Store Logo" hint="PNG or JPG square image up to 5MB" />
                <x-file-upload name="cover_image" label="Cover Photo / Banner" hint="PNG or JPG banner image up to 5MB" />
            </div>
        </div>

        {{-- Description / Bio --}}
        <x-textarea :label="$isBusiness ? 'About your business' : 'About your store'" name="description" :value="old('description')" rows="3" placeholder="Tell buyers about your products, quality assurance, and story..." required />

        {{-- Location Details: Country, State, City --}}
        <div class="bg-gray-50/50 p-4 border border-gray-100 rounded-xl space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Store Location</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                <x-select label="Country" name="country" id="seller_country_select" placeholder="Select Country" :selected="old('country', 'Bangladesh')" />
                <x-select label="State / Region (optional)" name="state" id="seller_state_select" placeholder="Select State" :selected="old('state')" />
                <x-select label="City" name="city" id="seller_city_select" placeholder="Select City" :selected="old('city')" />
            </div>
        </div>

        @if ($isBusiness)
            <div class="grid sm:grid-cols-2 gap-4">
                <x-input label="Business phone (optional)" name="phone" type="tel" :value="old('phone')" placeholder="+880 1700-000000" />
                <x-input label="Website (optional)" name="website" type="url" :value="old('website')" placeholder="https://yourstore.com" />
            </div>
        @endif

        {{-- Admin KYC Document Verification --}}
        @if (isset($requirements) && $requirements->isNotEmpty())
            <div class="bg-amber-50/40 p-4 border border-amber-200/60 rounded-xl space-y-4">
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">KYC Verification Documents</h2>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Admin requires the following document verification before your store can start publishing products.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach ($requirements as $req)
                        @php($typeKey = $req->document_type->value)
                        <div class="bg-white p-3.5 border border-gray-200 rounded-lg space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-800">{{ $req->document_type->label() }}</label>
                                @if ($req->is_required)
                                    <span class="px-2 py-0.5 text-[10px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-md">* Required</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-medium text-gray-500 bg-gray-100 rounded-md">Optional</span>
                                @endif
                            </div>
                            <x-file-upload :name="'kyc_documents[' . $typeKey . ']'" hint="PDF, JPG, PNG up to 10MB" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <x-button type="submit" class="w-full justify-center text-sm font-bold py-3">
            Finish Setup & Submit Profile
        </x-button>
    </form>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-control {
            border-radius: 0.375rem !important;
            border-color: #e2e8f0 !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
        }
        .ts-control.focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.4) !important;
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e2e8f0 !important;
            margin-top: 4px !important;
            z-index: 50 !important;
        }
        .ts-dropdown .option.active, .ts-dropdown .option:hover {
            background-color: #fffbeb !important;
            color: #d97706 !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fallbackCountries = [
                { country: 'Bangladesh', states: ['Dhaka Division', 'Chattogram Division', 'Rajshahi Division', 'Khulna Division', 'Barishal Division', 'Sylhet Division', 'Rangpur Division', 'Mymensingh Division'], cities: ['Dhaka', 'Chattogram', 'Rajshahi', 'Khulna', 'Barishal', 'Sylhet', 'Rangpur', 'Mymensingh', 'Cumilla', 'Gazipur', 'Narayanganj', 'Bogra'] },
                { country: 'United States', states: ['California', 'New York', 'Texas', 'Florida', 'Illinois', 'Washington', 'Georgia', 'North Carolina', 'Pennsylvania', 'Ohio'], cities: ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'] },
                { country: 'United Kingdom', states: ['England', 'Scotland', 'Wales', 'Northern Ireland'], cities: ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Bristol', 'Edinburgh', 'Leeds'] },
                { country: 'Canada', states: ['Ontario', 'Quebec', 'British Columbia', 'Alberta', 'Manitoba'], cities: ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa'] },
                { country: 'Australia', states: ['New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia'], cities: ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide'] },
                { country: 'Qatar', states: ['Doha', 'Al Rayyan', 'Al Wakrah', 'Al Khor', 'Umm Salal'], cities: ['Doha', 'Al Rayyan', 'Al Wakrah', 'Al Khor', 'Dukhan', 'Mesaieed'] },
                { country: 'Saudi Arabia', states: ['Riyadh Region', 'Makkah Region', 'Eastern Province', 'Madinah Region'], cities: ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar'] },
                { country: 'United Arab Emirates', states: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah'], cities: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Al Ain'] },
                { country: 'India', states: ['Delhi', 'Maharashtra', 'Karnataka', 'West Bengal', 'Tamil Nadu', 'Gujarat'], cities: ['Delhi', 'Mumbai', 'Bengaluru', 'Kolkata', 'Chennai', 'Hyderabad', 'Ahmedabad', 'Pune'] },
                { country: 'Germany', states: ['Bavaria', 'Berlin', 'North Rhine-Westphalia', 'Hesse', 'Baden-Württemberg'], cities: ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne', 'Stuttgart'] },
                { country: 'Singapore', states: ['Central Region', 'East Region', 'North Region'], cities: ['Singapore'] },
                { country: 'Malaysia', states: ['Kuala Lumpur', 'Selangor', 'Penang', 'Johor'], cities: ['Kuala Lumpur', 'George Town', 'Johor Bahru', 'Ipoh'] }
            ];

            let countriesData = fallbackCountries;

            const initialCountry = @json(old('country', $profile->country ?? 'Bangladesh'));
            const initialCity = @json(old('city', $profile->city ?? ''));
            const initialState = @json(old('state', $profile->state ?? ''));

            const countrySelect = document.getElementById('seller_country_select');
            const stateSelect = document.getElementById('seller_state_select');
            const citySelect = document.getElementById('seller_city_select');

            let countryTom = null;
            let stateTom = null;
            let cityTom = null;

            if (countrySelect) {
                countryTom = new TomSelect(countrySelect, {
                    create: false,
                    placeholder: 'Select Country',
                    onChange: function (val) {
                        updateStatesAndCities(val);
                    }
                });
            }

            if (stateSelect) {
                stateTom = new TomSelect(stateSelect, {
                    create: false,
                    placeholder: 'Select State'
                });
            }

            if (citySelect) {
                cityTom = new TomSelect(citySelect, {
                    create: false,
                    placeholder: 'Select City'
                });
            }

            function populateCountries() {
                if (!countryTom) return;
                countryTom.clearOptions();

                countriesData.forEach(item => {
                    countryTom.addOption({ value: item.country, text: item.country });
                });

                if (initialCountry) {
                    countryTom.setValue(initialCountry, true);
                    updateStatesAndCities(initialCountry, initialState, initialCity);
                }
            }

            function updateStatesAndCities(countryName, selState = '', selCity = '') {
                const countryObj = countriesData.find(c => c.country.toLowerCase() === (countryName || '').toLowerCase());
                const states = countryObj ? countryObj.states : [];
                const cities = countryObj ? countryObj.cities : [];

                if (stateTom) {
                    stateTom.clearOptions();
                    states.forEach(s => stateTom.addOption({ value: s, text: s }));
                    if (selState) stateTom.setValue(selState, true);
                }

                if (cityTom) {
                    cityTom.clearOptions();
                    cities.forEach(c => cityTom.addOption({ value: c, text: c }));
                    if (selCity) cityTom.setValue(selCity, true);
                }
            }

            populateCountries();
        });
    </script>
@endsection
