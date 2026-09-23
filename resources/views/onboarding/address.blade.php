@php($maxWidth = 'max-w-xl')
@extends('layouts.auth')

@section('title', 'Shipping Address & Verification')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs font-bold text-brand-600 uppercase tracking-wider mb-1">
            <span>Step 1 of 2</span>
            <span>•</span>
            <span>Shipping Address</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-1">Add Your Primary Address</h1>
        <p class="text-sm text-gray-500">
            Please enter your primary shipping address below. Once saved, we will send a verification link to your email.
        </p>
    </div>

    @if (session('status'))
        <x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('onboarding.address.store') }}" class="space-y-4">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            <x-input label="Full name" name="name" type="text" placeholder="Enter your full name" :value="old('name', $user->name)" />
            <x-input label="Phone" name="phone" type="tel" placeholder="e.g. +880 1700-000000" :value="old('phone', $user->phone)" />
            <x-input label="Address line 1" name="line1" type="text" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" :value="old('line1')" />
            <x-input label="Address line 2 (optional)" name="line2" type="text" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" :value="old('line2')" />
            
            <x-select label="Country" name="country" id="user_country_select" placeholder="Select Country" :selected="old('country', 'United States')" />
            <x-select label="State/Area (optional)" name="state" id="user_state_select" placeholder="Select State/Area" :selected="old('state')" />
            <x-select label="City" name="city" id="user_city_select" placeholder="Select City" :selected="old('city')" />
            <x-input label="Postal code (optional)" name="postal_code" type="text" placeholder="e.g. 1207" :value="old('postal_code')" />
        </div>

        <div class="pt-2">
            <x-button type="submit" class="w-full justify-center">
                Save Address & Send Verification Code
            </x-button>
        </div>
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

            const initialCountry = @json(old('country', 'United States'));
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
                        placeholder: 'Search & select country...',
                        allowEmptyOption: true,
                        onChange: function(val) {
                            populateCitiesAndStates(val, '', '');
                        }
                    });
                }

                if (citySelect && !cityTomSelect) {
                    cityTomSelect = new TomSelect(citySelect, {
                        create: true,
                        placeholder: 'Search & select city...',
                        allowEmptyOption: true,
                    });
                }

                if (stateSelect && !stateTomSelect) {
                    stateTomSelect = new TomSelect(stateSelect, {
                        create: true,
                        placeholder: 'Search & select state/area...',
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
                defaultCountryOpt.textContent = 'Search & select country...';
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
                    countryTomSelect.addOption({ value: '', text: 'Search & select country...' });

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
                    defaultCityOpt.textContent = countryName ? 'Search & select city...' : 'Select a country first...';
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
                        cityTomSelect.addOption({ value: '', text: countryName ? 'Search & select city...' : 'Select a country first...' });

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
                    defaultStateOpt.textContent = countryName ? 'Search & select state/area...' : 'Select a country first...';
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
                        stateTomSelect.addOption({ value: '', text: countryName ? 'Search & select state/area...' : 'Select a country first...' });

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
