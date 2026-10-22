@php($maxWidth = 'max-w-xl')
@php($cardPadding = 'px-8 py-14 sm:px-10 sm:py-16')
@extends('layouts.auth')

@section('title', 'Shipping Address & Verification')

@section('content')
    <div class="flex items-center gap-2 mb-6">
        <button
            type="button"
            onclick="window.history.length > 1 ? history.back() : (window.location = '{{ route('home') }}')"
            class="inline-flex items-center justify-center w-7 h-7 -ml-1 rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors shrink-0"
            aria-label="Go back"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <div class="flex items-center gap-1.5 flex-1">
            <div class="h-1.5 flex-1 rounded-full bg-brand-500"></div>
            <div class="h-1.5 flex-1 rounded-full bg-gray-100"></div>
        </div>
    </div>

    <div class="flex items-start gap-4 mb-11">
        <div class="shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center shadow-lg shadow-brand-500/20">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <div>
            <div class="text-xs font-bold text-brand-600 uppercase tracking-wider mb-1.5">Step 1 of 2 · Shipping Address</div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1.5">Add Your Primary Address</h1>
            <p class="text-sm text-gray-500 leading-relaxed">
                Enter your primary shipping address below. Once saved, we'll send a verification link to your email.
            </p>
        </div>
    </div>

    @if (session('status'))
        <x-alert type="success" class="mb-7">{{ session('status') }}</x-alert>
    @endif

    <form id="onboarding-address-form" method="POST" action="{{ route('onboarding.address.store') }}" class="space-y-11">
        @csrf

        <div class="space-y-7">
            <h2 class="flex items-center gap-2.5 text-sm font-bold text-gray-900">
                <span class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
                Contact Details
            </h2>
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Full name" name="name" type="text" autocomplete="name" placeholder="Enter your full name" :value="old('name', $user->name)" required />
                <x-input label="Phone" name="phone" type="tel" autocomplete="tel" placeholder="e.g. +880 1700-000000" :value="old('phone', $user->phone)" required />
            </div>
        </div>

        <div class="space-y-7">
            <h2 class="flex items-center gap-2.5 text-sm font-bold text-gray-900">
                <span class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                Delivery Address
            </h2>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <x-input label="Address line 1" name="line1" type="text" autocomplete="address-line1" placeholder="House / Building #, Street name, Area" :value="old('line1')" required />
                    <x-input label="Address line 2 (optional)" name="line2" type="text" autocomplete="address-line2" placeholder="Apartment, Suite, Unit, Floor (optional)" :value="old('line2')" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-select label="Country" name="country" id="user_country_select" autocomplete="off" placeholder="Select Country" :selected="old('country')" required />
                    <x-select label="State/Area (optional)" name="state" id="user_state_select" autocomplete="off" placeholder="Select State/Area" :selected="old('state')" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-select label="City" name="city" id="user_city_select" autocomplete="off" placeholder="Select City" :selected="old('city')" required />
                    <x-input label="Postal code (optional)" name="postal_code" type="text" autocomplete="off" placeholder="e.g. 1207" :value="old('postal_code')" />
                </div>
            </div>
        </div>

        <div class="pt-3">
            <x-button type="submit" class="w-full justify-center gap-2 py-3.5 text-sm font-semibold">
                Save Address & Send Verification Code
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12" />
                </svg>
            </x-button>
        </div>
    </form>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        #onboarding-address-form input::placeholder,
        #onboarding-address-form textarea::placeholder,
        #onboarding-address-form .ts-control input::placeholder,
        #onboarding-address-form .ts-control .item {
            color: #9ca3af !important;
            opacity: 1 !important;
            font-weight: 400 !important;
        }

        /* Prevent the browser's autofill color/background animation from
           visibly shifting these fields when autofill is applied. */
        #onboarding-address-form input:-webkit-autofill,
        #onboarding-address-form input:-webkit-autofill:hover,
        #onboarding-address-form input:-webkit-autofill:focus,
        #onboarding-address-form input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
            -webkit-text-fill-color: #1f2937 !important;
            transition: background-color 600000s 0s, color 600000s 0s !important;
        }

        /* Fixed control height so the country/state/city widgets can't
           resize (and shove the rest of the form down) once JS/autofill
           populates them with a value. */
        #onboarding-address-form .ts-wrapper,
        #onboarding-address-form .ts-control {
            min-height: 2.875rem !important;
        }

        .ts-wrapper {
            border: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .ts-control {
            border-radius: 0.375rem !important;
            border-color: #e2e8f0 !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
        }
        .ts-control.focus {
            border-color: #E5E7EB !important;
            box-shadow: 0 0 0 2px #E5E7EB !important;
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e2e8f0 !important;
            margin-top: 4px !important;
            z-index: 50 !important;
        }
        .ts-dropdown .option.active, .ts-dropdown .option:hover {
            background-color: var(--color-brand-50, #fffbeb) !important;
            color: var(--color-brand-600, #d97706) !important;
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
                { country: 'India', states: ['Delhi', 'Maharashtra', 'Karnataka', 'West Bengal', 'Tamil Nadu', 'Gujarat'], cities: ['Delhi', 'Mumbai', 'Bengaluru', 'Kolkata', 'Chennai', 'Hyderabad', 'Ahmedabad', 'Pune'] },
                { country: 'United Arab Emirates', states: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah'], cities: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Al Ain'] },
                { country: 'Saudi Arabia', states: ['Riyadh Region', 'Makkah Region', 'Eastern Province', 'Madinah Region'], cities: ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar'] },
                { country: 'Germany', states: ['Bavaria', 'Berlin', 'North Rhine-Westphalia', 'Hesse', 'Baden-Württemberg'], cities: ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne', 'Stuttgart'] },
                { country: 'France', states: ['Île-de-France', 'Auvergne-Rhône-Alpes', 'Provence-Alpes-Côte d\'Azur'], cities: ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes'] },
                { country: 'Singapore', states: ['Central Region', 'East Region', 'North Region'], cities: ['Singapore'] },
                { country: 'Malaysia', states: ['Kuala Lumpur', 'Selangor', 'Penang', 'Johor'], cities: ['Kuala Lumpur', 'George Town', 'Johor Bahru', 'Ipoh'] },
                { country: 'Pakistan', states: ['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan'], cities: ['Karachi', 'Lahore', 'Faisalabad', 'Rawalpindi', 'Islamabad'] }
            ];

            let countriesData = fallbackCountries;
            let statesData = fallbackCountries.map(item => ({ name: item.country, states: item.states }));

            const initialCountry = @json(old('country', ''));
            const initialCity = @json(old('city', ''));
            const initialState = @json(old('state', ''));

            const countrySelect = document.getElementById('user_country_select');
            const citySelect = document.getElementById('user_city_select');
            const stateSelect = document.getElementById('user_state_select');

            let countryTomSelect = null;
            let cityTomSelect = null;
            let stateTomSelect = null;

            function initTomSelectInstances() {
                if (typeof TomSelect === 'undefined') return;

                if (countrySelect && !countryTomSelect) {
                    countryTomSelect = new TomSelect(countrySelect, {
                        create: true,
                        placeholder: 'Select Country',
                        allowEmptyOption: true,
                        onChange: function(val) {
                            populateCitiesAndStates(val, '', '');
                        }
                    });
                }

                if (citySelect && !cityTomSelect) {
                    cityTomSelect = new TomSelect(citySelect, {
                        create: true,
                        placeholder: 'Select City',
                        allowEmptyOption: true,
                    });
                }

                if (stateSelect && !stateTomSelect) {
                    stateTomSelect = new TomSelect(stateSelect, {
                        create: true,
                        placeholder: 'Select State/Area',
                        allowEmptyOption: true,
                    });
                }
            }

            function populateCountries(selectedCountryName) {
                if (!countrySelect) return;

                // Sync native select element options
                countrySelect.innerHTML = '';
                const defaultCountryOpt = document.createElement('option');
                defaultCountryOpt.value = '';
                defaultCountryOpt.textContent = 'Select Country';
                countrySelect.appendChild(defaultCountryOpt);

                countriesData.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.country;
                    opt.textContent = item.country;
                    if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                        opt.selected = true;
                    }
                    countrySelect.appendChild(opt);
                });

                if (countryTomSelect) {
                    countryTomSelect.clearOptions();
                    countryTomSelect.addOption({ value: '', text: 'Select Country' });

                    let matchFound = false;
                    countriesData.forEach(item => {
                        countryTomSelect.addOption({ value: item.country, text: item.country });
                        if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                            matchFound = true;
                            selectedCountryName = item.country;
                        }
                    });

                    if (selectedCountryName && !matchFound) {
                        countryTomSelect.addOption({ value: selectedCountryName, text: selectedCountryName });
                    }

                    countryTomSelect.refreshOptions(false);
                    countryTomSelect.setValue(selectedCountryName || '', true);
                }
            }

            function populateCitiesAndStates(countryName, selectedCityName, selectedStateName) {
                if (citySelect) {
                    const countryObj = countryName ? countriesData.find(item => item.country.toLowerCase() === countryName.toLowerCase()) : null;
                    const cities = countryObj ? (countryObj.cities || []) : [];

                    // Sync native select options
                    citySelect.innerHTML = '';
                    const defaultCityOpt = document.createElement('option');
                    defaultCityOpt.value = '';
                    defaultCityOpt.textContent = 'Select City';
                    citySelect.appendChild(defaultCityOpt);

                    cities.forEach(cityName => {
                        const opt = document.createElement('option');
                        opt.value = cityName;
                        opt.textContent = cityName;
                        if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                            opt.selected = true;
                        }
                        citySelect.appendChild(opt);
                    });

                    if (cityTomSelect) {
                        cityTomSelect.clearOptions();
                        cityTomSelect.addOption({ value: '', text: 'Select City' });

                        let matchFound = false;
                        cities.forEach(cityName => {
                            cityTomSelect.addOption({ value: cityName, text: cityName });
                            if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                                matchFound = true;
                                selectedCityName = cityName;
                            }
                        });

                        if (selectedCityName && !matchFound) {
                            cityTomSelect.addOption({ value: selectedCityName, text: selectedCityName });
                        }

                        cityTomSelect.refreshOptions(false);
                        cityTomSelect.setValue(selectedCityName || '', true);
                    }
                }

                if (stateSelect) {
                    const stateObj = countryName ? statesData.find(item => (item.name || item.country || '').toLowerCase() === countryName.toLowerCase()) : null;
                    const states = stateObj ? (stateObj.states || []) : [];

                    // Sync native select options
                    stateSelect.innerHTML = '';
                    const defaultStateOpt = document.createElement('option');
                    defaultStateOpt.value = '';
                    defaultStateOpt.textContent = 'Select State/Area';
                    stateSelect.appendChild(defaultStateOpt);

                    states.forEach(st => {
                        const sName = typeof st === 'string' ? st : (st.name || st);
                        const opt = document.createElement('option');
                        opt.value = sName;
                        opt.textContent = sName;
                        if (selectedStateName && sName.toLowerCase() === selectedStateName.toLowerCase()) {
                            opt.selected = true;
                        }
                        stateSelect.appendChild(opt);
                    });

                    if (stateTomSelect) {
                        stateTomSelect.clearOptions();
                        stateTomSelect.addOption({ value: '', text: 'Select State/Area' });

                        let matchFound = false;
                        states.forEach(st => {
                            const sName = typeof st === 'string' ? st : (st.name || st);
                            stateTomSelect.addOption({ value: sName, text: sName });
                            if (selectedStateName && sName.toLowerCase() === selectedStateName.toLowerCase()) {
                                matchFound = true;
                                selectedStateName = sName;
                            }
                        });

                        if (selectedStateName && !matchFound) {
                            stateTomSelect.addOption({ value: selectedStateName, text: selectedStateName });
                        }

                        stateTomSelect.refreshOptions(false);
                        stateTomSelect.setValue(selectedStateName || '', true);
                    }
                }
            }

            // Initialize IMMEDIATELY on load
            initTomSelectInstances();
            populateCountries(initialCountry);
            populateCitiesAndStates(initialCountry, initialCity, initialState);

            // Asynchronously fetch full database in background
            async function fetchFullDatabaseInBackground() {
                try {
                    const [resCountries, resStates] = await Promise.all([
                        fetch('https://countriesnow.space/api/v0.1/countries'),
                        fetch('https://countriesnow.space/api/v0.1/countries/states')
                    ]);

                    const jsonCountries = await resCountries.json();
                    const jsonStates = await resStates.json();

                    if (jsonCountries && jsonCountries.data && jsonCountries.data.length > 0) {
                        countriesData = jsonCountries.data;
                    }

                    if (jsonStates && jsonStates.data && jsonStates.data.length > 0) {
                        statesData = jsonStates.data;
                    }

                    const currentSelectedCountry = countryTomSelect ? countryTomSelect.getValue() : initialCountry;
                    const currentSelectedCity = cityTomSelect ? cityTomSelect.getValue() : initialCity;
                    const currentSelectedState = stateTomSelect ? stateTomSelect.getValue() : initialState;

                    populateCountries(currentSelectedCountry);
                    populateCitiesAndStates(currentSelectedCountry, currentSelectedCity, currentSelectedState);
                } catch (err) {
                    // Silently retain fallback data
                }
            }

            fetchFullDatabaseInBackground();
        });
    </script>
@endsection
