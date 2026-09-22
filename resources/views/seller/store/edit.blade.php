@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.' : 'saler.';
    $storeUrl = ($profile && !empty($profile->slug)) 
        ? ($isBusiness ? route('stores.business', $profile->slug) : route('stores.saler', $profile->slug)) 
        : '#';
@endphp

@extends($isBusiness ? 'layouts.business' : 'layouts.saler')

@section('title', $isBusiness ? 'Store Profile' : 'Seller Profile')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>{{ $isBusiness ? 'Store Profile' : 'Seller Profile' }}</x-slot:title>
        <x-slot:action>
            @if ($storeUrl !== '#')
                <a href="{{ $storeUrl }}" target="_blank" class="text-sm text-brand-600 font-medium hover:underline">{{ $isBusiness ? 'View live store →' : 'View live profile →' }}</a>
            @endif
        </x-slot:action>

        <form method="POST" action="{{ route($routePrefix.'store.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if ($isBusiness)
                <x-input label="Business name" name="business_name" type="text" :value="old('business_name', $profile->business_name)" />
                <x-textarea label="Description" name="description" rows="4" :value="old('description', $profile->description)" />
            @else
                <x-input label="Display name" name="display_name" type="text" :value="old('display_name', $profile->display_name)" />
                <x-textarea label="Bio" name="bio" rows="4" :value="old('bio', $profile->bio)" />
            @endif

            <div class="grid sm:grid-cols-2 gap-5">
                @php
                    $currentPhoto = $profile->logo ?? $profile->profile_photo ?? null;
                    $getStorageUrl = function ($path) {
                        if (empty($path)) return null;
                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                            return $path;
                        }
                        $path = ltrim($path, '/');
                        if (str_starts_with($path, 'public/')) {
                            $path = substr($path, 7);
                        }
                        if (str_starts_with($path, 'storage/')) {
                            return asset($path);
                        }
                        return Illuminate\Support\Facades\Storage::disk('public')->url($path);
                    };
                @endphp
                <div>
                    <x-file-upload 
                        :name="$isBusiness ? 'logo' : 'profile_photo'" 
                        :label="$isBusiness ? 'Logo' : 'Profile photo'" 
                        :value="$getStorageUrl($currentPhoto)" 
                    />
                </div>
                <div>
                    <x-file-upload 
                        name="cover_image" 
                        label="Cover image" 
                        hint="Wide banner, 1200×300 recommended" 
                        :value="$getStorageUrl($profile->cover_image ?? null)" 
                    />
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                @if ($isBusiness)
                    <x-input label="Address" name="address" type="text" id="seller_address_input" placeholder="Search address or location on Google Maps..." :value="old('address', $profile->address)" />
                @else
                    <x-input label="Location" name="location" type="text" id="seller_location_input" placeholder="Search location on Google Maps..." :value="old('location', $profile->location)" />
                @endif
                <x-select label="Country" name="country" id="seller_country_select" placeholder="Loading countries..." :selected="old('country', $profile->country)" />
                <x-select label="City" name="city" id="seller_city_select" placeholder="Select a country first..." :selected="old('city', $profile->city)" />

                @if ($isBusiness)
                    <x-input label="Phone" name="phone" type="tel" :value="old('phone', $profile->phone)" />
                    <x-input label="Website" name="website" type="text" placeholder="https://yourstore.com" :value="old('website', $profile->website ?? '')" />
                @endif
            </div>

            {{-- Interactive Google Map Canvas --}}
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-gray-700">Location Map</label>
                <div id="seller_map_canvas" class="w-full h-64 sm:h-80 rounded-xl border border-gray-200 shadow-2xs overflow-hidden bg-gray-100"></div>
                <p class="text-xs text-gray-400">Search for a location above or click and drag the pin on the map to mark your location.</p>
            </div>

            @if ($isBusiness)
                @php($social = is_array($profile->social_links) ? $profile->social_links : (json_decode($profile->social_links ?? '', true) ?: []))
                <div class="grid sm:grid-cols-3 gap-5">
                    <x-input label="Instagram" name="social_links[instagram]" type="text" placeholder="https://instagram.com/yourstore or @handle" :value="old('social_links.instagram', $social['instagram'] ?? '')" />
                    <x-input label="Twitter / X" name="social_links[twitter]" type="text" placeholder="https://x.com/yourstore or @handle" :value="old('social_links.twitter', $social['twitter'] ?? '')" />
                    <x-input label="WhatsApp" name="social_links[whatsapp]" type="text" placeholder="+97455001122" :value="old('social_links.whatsapp', $social['whatsapp'] ?? '')" />
                </div>
            @endif

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_store_active" value="1" @checked(old('is_store_active', $profile->is_store_active)) class="w-4 h-4 rounded accent-brand-500">
                Store is publicly visible
            </label>

            <x-button type="submit">{{ $isBusiness ? 'Save Store Settings' : 'Save Profile Settings' }}</x-button>
        </form>
    </x-card>

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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCsdLSxCzJS1DypOOyGan4BWTZvZIhiS9M&libraries=places"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let countriesData = [];
            const initialCountry = @json(old('country', $profile->country ?? ''));
            const initialCity = @json(old('city', $profile->city ?? ''));

            const countrySelect = document.getElementById('seller_country_select') || document.getElementById('country');
            const citySelect = document.getElementById('seller_city_select') || document.getElementById('city');
            const locationInput = document.getElementById('seller_location_input') || document.getElementById('seller_address_input') || document.getElementById('location') || document.getElementById('address');

            let countryTomSelect = null;
            let cityTomSelect = null;

            function initTomSelectInstances() {
                if (typeof TomSelect === 'undefined') return;

                if (countrySelect && !countryTomSelect) {
                    countryTomSelect = new TomSelect(countrySelect, {
                        create: false,
                        placeholder: 'Search & select country...',
                        allowEmptyOption: true,
                        onChange: function(val) {
                            populateCities(val, '');
                        }
                    });
                }

                if (citySelect && !cityTomSelect) {
                    cityTomSelect = new TomSelect(citySelect, {
                        create: false,
                        placeholder: 'Search & select city...',
                        allowEmptyOption: true,
                    });
                }
            }

            let map, marker, geocoder;
            const defaultCenter = { lat: 23.8103, lng: 90.4125 };

            async function loadCountriesDatabase() {
                if (!countrySelect || !citySelect) return;

                try {
                    const response = await fetch('https://countriesnow.space/api/v0.1/countries');
                    const json = await response.json();

                    if (json && json.data) {
                        countriesData = json.data;
                        initTomSelectInstances();
                        populateCountries(initialCountry);
                        if (initialCountry) {
                            populateCities(initialCountry, initialCity);
                        }
                    }
                } catch (err) {
                    console.warn('Unable to load online countries database, using fallback:', err);
                    initTomSelectInstances();
                    populateCountries(initialCountry);
                    if (initialCity) {
                        populateCities(initialCountry, initialCity);
                    }
                }
            }

            function populateCountries(selectedCountryName) {
                if (!countrySelect) return;

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

                    countryTomSelect.setValue(selectedCountryName || '', true);
                    return;
                }

                countrySelect.innerHTML = '<option value="">Select Country</option>';
                let matchFound = false;
                countriesData.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.country;
                    opt.textContent = item.country;
                    if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                        opt.selected = true;
                        matchFound = true;
                    }
                    countrySelect.appendChild(opt);
                });

                if (selectedCountryName && !matchFound) {
                    const customOpt = document.createElement('option');
                    customOpt.value = selectedCountryName;
                    customOpt.textContent = selectedCountryName;
                    customOpt.selected = true;
                    countrySelect.appendChild(customOpt);
                }
            }

            function populateCities(countryName, selectedCityName) {
                if (!citySelect) return;

                const countryObj = countryName ? countriesData.find(item => item.country.toLowerCase() === countryName.toLowerCase()) : null;
                const cities = countryObj ? countryObj.cities : [];

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

                    cityTomSelect.setValue(selectedCityName || '', true);
                    return;
                }

                citySelect.innerHTML = '<option value="">Select City</option>';
                if (!countryName) return;

                let matchFound = false;
                cities.forEach(cityName => {
                    const opt = document.createElement('option');
                    opt.value = cityName;
                    opt.textContent = cityName;
                    if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                        opt.selected = true;
                        matchFound = true;
                    }
                    citySelect.appendChild(opt);
                });

                if (selectedCityName && !matchFound) {
                    const customOpt = document.createElement('option');
                    customOpt.value = selectedCityName;
                    customOpt.textContent = selectedCityName;
                    customOpt.selected = true;
                    citySelect.appendChild(customOpt);
                }
            }

            if (countrySelect && !countryTomSelect) {
                countrySelect.addEventListener('change', function () {
                    populateCities(this.value, '');
                });
            }

            loadCountriesDatabase();

            // Initialize Google Map & Interactive Marker
            const mapCanvas = document.getElementById('seller_map_canvas');
            if (mapCanvas && typeof google !== 'undefined' && google.maps) {
                geocoder = new google.maps.Geocoder();

                map = new google.maps.Map(mapCanvas, {
                    center: defaultCenter,
                    zoom: 12,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                });

                marker = new google.maps.Marker({
                    map: map,
                    position: defaultCenter,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                    title: 'Store Location'
                });

                const initialLoc = locationInput ? locationInput.value : '';
                if (initialLoc) {
                    geocodeAddress(initialLoc);
                }

                marker.addListener('dragend', function (e) {
                    geocodePosition(e.latLng);
                });

                map.addListener('click', function (e) {
                    marker.setPosition(e.latLng);
                    geocodePosition(e.latLng);
                });
            }

            function geocodeAddress(addressStr) {
                if (!geocoder || !addressStr) return;
                geocoder.geocode({ address: addressStr }, function (results, status) {
                    if (status === 'OK' && results[0] && map && marker) {
                        const loc = results[0].geometry.location;
                        map.setCenter(loc);
                        map.setZoom(14);
                        marker.setPosition(loc);
                    }
                });
            }

            function geocodePosition(pos) {
                if (!geocoder) return;
                geocoder.geocode({ location: pos }, function (results, status) {
                    if (status === 'OK' && results[0]) {
                        const place = results[0];
                        if (locationInput) {
                            locationInput.value = place.formatted_address;
                        }

                        let city = '';
                        let country = '';

                        for (const component of place.address_components) {
                            const types = component.types;
                            if (types.includes('locality')) {
                                city = component.long_name;
                            } else if (!city && (types.includes('administrative_area_level_2') || types.includes('postal_town') || types.includes('sublocality_level_1') || types.includes('administrative_area_level_1'))) {
                                city = component.long_name;
                            }

                            if (types.includes('country')) {
                                country = component.long_name;
                            }
                        }

                        if (country) {
                            populateCountries(country);
                            populateCities(country, city);
                        }
                    }
                });
            }

            // Google Maps Places Autocomplete Integration
            if (locationInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
                const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                    types: ['geocode', 'establishment'],
                });

                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    if (!place || !place.address_components) return;

                    if (place.geometry && place.geometry.location && map && marker) {
                        map.setCenter(place.geometry.location);
                        map.setZoom(15);
                        marker.setPosition(place.geometry.location);
                    }

                    let city = '';
                    let country = '';

                    for (const component of place.address_components) {
                        const types = component.types;

                        if (types.includes('locality')) {
                            city = component.long_name;
                        } else if (!city && (types.includes('administrative_area_level_2') || types.includes('postal_town') || types.includes('sublocality_level_1') || types.includes('administrative_area_level_1'))) {
                            city = component.long_name;
                        }

                        if (types.includes('country')) {
                            country = component.long_name;
                        }
                    }

                    if (country) {
                        populateCountries(country);
                        populateCities(country, city);
                    }
                });
            }
        });
    </script>
@endsection
