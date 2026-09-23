@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        @if (session('status'))
            <x-alert type="success">{{ session('status') }}</x-alert>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Saved Addresses</h1>
                <p class="text-xs text-gray-500 mt-0.5">Manage your delivery locations and set your primary shipping address.</p>
            </div>
            <button type="button" 
                    onclick="document.getElementById('add-address-modal').showModal()" 
                    id="add-address-btn"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2.5 shadow-sm transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Add New Address</span>
            </button>
        </div>

        @if ($addresses->isEmpty())
            <x-card class="text-center py-12">
                <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">No saved addresses found</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto mb-5">Add your primary shipping address to speed up your checkout process.</p>
                <button type="button" 
                        onclick="document.getElementById('add-address-modal').showModal()" 
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2.5">
                    + Add New Address
                </button>
            </x-card>
        @else
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($addresses as $address)
                    <div @class([
                        'border rounded-2xl p-5 relative transition-all duration-200 bg-white flex flex-col justify-between gap-4',
                        'border-amber-400 ring-2 ring-amber-400/20 shadow-md' => $address->is_default,
                        'border-gray-200/80 hover:border-gray-300 shadow-xs' => ! $address->is_default,
                    ])>
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-gray-900 text-base">{{ $address->label ?: ($address->is_default ? 'Primary Address' : 'Address #'.$loop->iteration) }}</span>
                                    @if ($address->is_default)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            <svg class="w-3 h-3 text-amber-600 fill-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            PRIMARY ADDRESS
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" 
                                            onclick="document.getElementById('edit-address-modal-{{ $address->id }}').showModal()"
                                            class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-brand-50 transition-colors"
                                            title="Edit Address">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Delete Address">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 space-y-1">
                                <p class="font-semibold text-gray-900 text-sm">{{ $address->name }}</p>
                                <p class="text-gray-500">{{ $address->phone }}</p>
                                <div class="pt-1 text-gray-700 leading-relaxed border-t border-gray-100">
                                    <p>{{ $address->line1 }}</p>
                                    @if ($address->line2)
                                        <p>{{ $address->line2 }}</p>
                                    @endif
                                    <p class="font-medium text-gray-900 mt-0.5">
                                        {{ implode(', ', array_filter([$address->city, $address->state, $address->country, $address->postal_code])) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                            @if ($address->is_default)
                                <span class="text-xs text-amber-700 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Default Delivery Address
                                </span>
                            @else
                                <form method="POST" action="{{ route('account.addresses.set-default', $address) }}" class="w-full">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-amber-800 bg-gray-50 hover:bg-amber-50 border border-gray-200 hover:border-amber-300 py-1.5 px-3 rounded-xl transition-all">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                        <span>Update as Primary Address</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Edit Address Modal Popup -->
                    <dialog id="edit-address-modal-{{ $address->id }}" class="rounded-2xl p-0 backdrop:bg-gray-900/50 backdrop:backdrop-blur-xs max-w-xl w-full border-0 shadow-2xl">
                        <div class="p-6 bg-white rounded-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <h3 class="font-bold text-gray-900 text-lg">Edit Address</h3>
                                <button type="button" onclick="document.getElementById('edit-address-modal-{{ $address->id }}').close()" class="text-gray-400 hover:text-gray-600 text-xl font-bold px-2">✕</button>
                            </div>

                            <form method="POST" action="{{ route('account.addresses.update', $address) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <x-input label="Label (optional)" name="label" placeholder="Home, Office, Warehouse..." :value="old('label', $address->label)" />
                                    <x-input label="Full Name" name="name" placeholder="Full recipient name" :value="old('name', $address->name)" />
                                    <x-input label="Phone" name="phone" placeholder="e.g. +880 1700-000000" :value="old('phone', $address->phone)" />
                                    <x-input label="Address line 1" name="line1" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" :value="old('line1', $address->line1)" />
                                    <x-input label="Address line 2 (optional)" name="line2" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" :value="old('line2', $address->line2)" />
                                    
                                    <x-select label="Country" name="country" id="edit_country_select_{{ $address->id }}" placeholder="Select Country" :selected="old('country', $address->country)" />
                                    <x-select label="State/Area (optional)" name="state" id="edit_state_select_{{ $address->id }}" placeholder="Select State/Area" :selected="old('state', $address->state)" />
                                    <x-select label="City" name="city" id="edit_city_select_{{ $address->id }}" placeholder="Select City" :selected="old('city', $address->city)" />
                                    <x-input label="Postal code (optional)" name="postal_code" placeholder="e.g. 1207" :value="old('postal_code', $address->postal_code)" />
                                </div>

                                <div class="pt-2">
                                    <x-checkbox name="is_default" :checked="$address->is_default">Set as primary shipping address</x-checkbox>
                                </div>

                                <div class="pt-3 flex justify-end gap-3 border-t border-gray-100">
                                    <x-button type="button" variant="secondary" onclick="document.getElementById('edit-address-modal-{{ $address->id }}').close()">Cancel</x-button>
                                    <x-button type="submit">Update Address</x-button>
                                </div>
                            </form>
                        </div>
                    </dialog>
                @endforeach
            </div>
        @endif

        <!-- Add Address Modal Popup -->
        <dialog id="add-address-modal" class="rounded-2xl p-0 backdrop:bg-gray-900/50 backdrop:backdrop-blur-xs max-w-xl w-full border-0 shadow-2xl">
            <div class="p-6 bg-white rounded-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Add New Shipping Address</h3>
                        <p class="text-xs text-gray-500">Fill out your shipping address details below.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('add-address-modal').close()" class="text-gray-400 hover:text-gray-600 text-xl font-bold px-2">✕</button>
                </div>

                @if ($errors->any())
                    <x-alert type="error">
                        <p class="font-semibold">Please correct the errors below:</p>
                        <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('account.addresses.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-input label="Address Label (optional)" name="label" placeholder="Home, Office, Warehouse..." :value="old('label')" />
                        <x-input label="Full Name" name="name" placeholder="Full recipient name" :value="old('name', Auth::user()->name)" />
                        <x-input label="Phone" name="phone" placeholder="e.g. +880 1700-000000" :value="old('phone', Auth::user()->phone)" />
                        <x-input label="Address line 1" name="line1" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" :value="old('line1')" />
                        <x-input label="Address line 2 (optional)" name="line2" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" :value="old('line2')" />
                        
                        <x-select label="Country" name="country" id="add_country_select" placeholder="Select Country" :selected="old('country', 'United States')" />
                        <x-select label="State/Area (optional)" name="state" id="add_state_select" placeholder="Select State/Area" :selected="old('state')" />
                        <x-select label="City" name="city" id="add_city_select" placeholder="Select City" :selected="old('city')" />
                        <x-input label="Postal code (optional)" name="postal_code" placeholder="e.g. 1207" :value="old('postal_code')" />
                    </div>

                    <div class="pt-2">
                        <x-checkbox name="is_default" :checked="$addresses->isEmpty()">Set as primary delivery address</x-checkbox>
                    </div>

                    <div class="pt-3 flex justify-end gap-3 border-t border-gray-100">
                        <x-button type="button" variant="secondary" onclick="document.getElementById('add-address-modal').close()">Cancel</x-button>
                        <x-button type="submit">Save & Add Address</x-button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

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
            z-index: 9999 !important;
        }
        .ts-dropdown .option.active, .ts-dropdown .option:hover {
            background-color: #fffbeb !important;
            color: #d97706 !important;
        }
        dialog::backdrop {
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($errors->any())
                const addModal = document.getElementById('add-address-modal');
                if (addModal) {
                    addModal.showModal();
                }
            @endif

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
            const refreshCallbacks = [];

            function setupGeographicDropdowns(countryId, stateId, cityId, initialCountry, initialState, initialCity) {
                const countrySelect = document.getElementById(countryId);
                const stateSelect = document.getElementById(stateId);
                const citySelect = document.getElementById(cityId);

                if (!countrySelect) return;

                let countryTomSelect = null;
                let cityTomSelect = null;
                let stateTomSelect = null;

                if (typeof TomSelect !== 'undefined') {
                    if (countrySelect && !countrySelect.tomselect) {
                        countryTomSelect = new TomSelect(countrySelect, {
                            create: true,
                            placeholder: 'Search & select country...',
                            allowEmptyOption: true,
                            onChange: function(val) {
                                populateCitiesAndStates(val, '', '');
                            }
                        });
                    } else if (countrySelect.tomselect) {
                        countryTomSelect = countrySelect.tomselect;
                    }

                    if (citySelect && !citySelect.tomselect) {
                        cityTomSelect = new TomSelect(citySelect, {
                            create: true,
                            placeholder: 'Search & select city...',
                            allowEmptyOption: true,
                        });
                    } else if (citySelect && citySelect.tomselect) {
                        cityTomSelect = citySelect.tomselect;
                    }

                    if (stateSelect && !stateSelect.tomselect) {
                        stateTomSelect = new TomSelect(stateSelect, {
                            create: true,
                            placeholder: 'Search & select state/area...',
                            allowEmptyOption: true,
                        });
                    } else if (stateSelect && stateSelect.tomselect) {
                        stateTomSelect = stateSelect.tomselect;
                    }
                }

                function populateCountries(selectedCountryName) {
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

                populateCountries(initialCountry);
                populateCitiesAndStates(initialCountry, initialCity, initialState);

                refreshCallbacks.push(() => {
                    const cVal = countryTomSelect ? countryTomSelect.getValue() : initialCountry;
                    const ctVal = cityTomSelect ? cityTomSelect.getValue() : initialCity;
                    const stVal = stateTomSelect ? stateTomSelect.getValue() : initialState;
                    populateCountries(cVal);
                    populateCitiesAndStates(cVal, ctVal, stVal);
                });
            }

            // Setup Add Form
            setupGeographicDropdowns(
                'add_country_select', 
                'add_state_select', 
                'add_city_select', 
                @json(old('country', 'United States')), 
                @json(old('state', '')), 
                @json(old('city', ''))
            );

            // Setup Edit Modals
            @foreach ($addresses as $addr)
                setupGeographicDropdowns(
                    'edit_country_select_{{ $addr->id }}', 
                    'edit_state_select_{{ $addr->id }}', 
                    'edit_city_select_{{ $addr->id }}', 
                    @json(old('country', $addr->country)), 
                    @json(old('state', $addr->state)), 
                    @json(old('city', $addr->city))
                );
            @endforeach

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

                    refreshCallbacks.forEach(cb => cb());
                } catch (err) {
                    // Silently retain fallback data
                }
            }

            fetchFullDatabaseInBackground();
        });
    </script>
@endsection
