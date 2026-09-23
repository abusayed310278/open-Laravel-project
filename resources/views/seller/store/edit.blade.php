@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.' : 'saler.';
    $storeUrl = ($profile && !empty($profile->slug))
        ? ($isBusiness ? route('stores.business', $profile->slug) : route('stores.saler', $profile->slug))
        : '#';
    $socialLinksList = \App\Support\SocialLinkNormalizer::normalizeForDisplay(old('social_links', $profile->social_links ?? []));
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
                <div class="max-w-[220px]">
                    <x-file-upload
                        :name="$isBusiness ? 'logo' : 'profile_photo'"
                        label="Store Logo"
                        hint="Recommended 400×400 square image"
                        :value="$getStorageUrl($currentPhoto)"
                        fill
                    />
                </div>
                <div>
                    <x-file-upload
                        name="cover_image"
                        label="Cover Photo / Banner"
                        hint="Recommended 1200×600 banner"
                        :value="$getStorageUrl($profile->cover_image ?? null)"
                    />
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="Search Location" name="location_search" type="text" id="seller_location_search" placeholder="Search location on Google Maps..." value="" />

                @if ($isBusiness)
                    <x-input label="Address" name="address" type="text" id="seller_address_input" placeholder="Store address..." :value="old('address', $profile->address)" />
                @else
                    <x-input label="Location" name="location" type="text" id="seller_location_input" placeholder="Seller location..." :value="old('location', $profile->location)" />
                @endif

                <x-select label="Country" name="country" id="seller_country_select" placeholder="Loading countries..." :selected="old('country', $profile->country)" />
                <x-select label="City" name="city" id="seller_city_select" placeholder="Select a country first..." :selected="old('city', $profile->city)" />

                <x-input label="Latitude" name="latitude" type="number" step="any" min="-90" max="90" id="seller_latitude_input" placeholder="e.g. 23.8103" :value="old('latitude', $profile->latitude)" />
                <x-input label="Longitude" name="longitude" type="number" step="any" min="-180" max="180" id="seller_longitude_input" placeholder="e.g. 90.4125" :value="old('longitude', $profile->longitude)" />

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

            {{-- Business Hours --}}
            @php
                $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $storedHours = old('business_hours', $profile->business_hours ?? []);
                $businessHours = [];
                foreach ($weekDays as $day) {
                    $row = is_array($storedHours[$day] ?? null) ? $storedHours[$day] : [];
                    $businessHours[$day] = [
                        'enabled' => array_key_exists('enabled', $row) ? (bool) $row['enabled'] : true,
                        'open' => $row['open'] ?? '08:00',
                        'close' => $row['close'] ?? '17:00',
                    ];
                }
            @endphp
            <div class="border-t border-gray-100 pt-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-full bg-brand-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Business Hours</h3>
                            <p class="text-xs text-gray-400">Set operating schedule and local timezone</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-medium text-gray-500">Set All:</span>
                        <input type="time" id="bh_set_all_open" value="08:00" class="border border-gray-200 rounded-md px-2.5 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <span class="text-gray-300">–</span>
                        <input type="time" id="bh_set_all_close" value="17:00" class="border border-gray-200 rounded-md px-2.5 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <button type="button" id="bh_apply_all_btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-md transition cursor-pointer">
                            Apply to All Days
                        </button>
                    </div>
                </div>

                <div class="max-w-xs">
                    <x-select label="Timezone" name="timezone" :options="$timezoneOptions" :selected="old('timezone', $profile->timezone ?? config('app.timezone'))" />
                    <p class="text-xs text-gray-400 mt-1">Required for accurate live open/closed status</p>
                </div>

                <div class="border border-gray-200 rounded-xl divide-y divide-gray-100 overflow-hidden">
                    @foreach ($businessHours as $day => $hours)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 js-bh-row">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-gray-700 min-w-[130px]">
                                <input type="hidden" name="business_hours[{{ $day }}][enabled]" value="0">
                                <input type="checkbox" name="business_hours[{{ $day }}][enabled]" value="1" @checked($hours['enabled']) class="js-bh-enabled w-4 h-4 rounded accent-brand-500">
                                {{ ucfirst($day) }}
                            </label>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-xs text-gray-400">Open:</span>
                                <input type="time" name="business_hours[{{ $day }}][open]" value="{{ $hours['open'] }}" class="js-bh-open border border-gray-200 rounded-md px-2.5 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <span class="text-gray-300">–</span>
                                <span class="text-xs text-gray-400">Close:</span>
                                <input type="time" name="business_hours[{{ $day }}][close]" value="{{ $hours['close'] }}" class="js-bh-close border border-gray-200 rounded-md px-2.5 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Social Media Links --}}
            <div class="border-t border-gray-100 pt-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-800">Social Media Links</h3>
                    <button type="button" id="social_add_link_btn" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Link
                    </button>
                </div>

                <div id="social_links_list" class="space-y-2.5"></div>

            </div>

            {{-- KYC Verification & Submitted Documents Section --}}
            @if (isset($kycApplication))
                @php
                    $submittedDocs = $kycApplication->documents ?? collect();
                    $verificationRoute = Route::has($routePrefix . 'verification.notice') ? route($routePrefix . 'verification.notice') : '#';
                @endphp
                <div class="border-t border-gray-100 pt-6 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-amber-50/60 p-4 border border-amber-200/80 rounded-xl">
                        <div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">KYC Verification & Uploaded Documents</h3>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">Review the verification documents submitted for your account and check approval status.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 font-medium">Status:</span>
                            <x-badge :color="$kycApplication->status->badgeColor()">{{ $kycApplication->status->label() }}</x-badge>
                            @if ($verificationRoute !== '#')
                                <a href="{{ $verificationRoute }}" class="inline-flex items-center gap-1 text-xs text-amber-800 font-bold hover:underline bg-white px-3 py-1.5 rounded-lg border border-amber-300">
                                    Manage Verification →
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($submittedDocs->isNotEmpty())
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach ($submittedDocs as $doc)
                                @php
                                    $docDownloadUrl = Route::has($routePrefix . 'verification.document') ? route($routePrefix . 'verification.document', $doc) : '#';
                                @endphp
                                <div class="p-3.5 bg-white border border-gray-200 rounded-xl flex items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-lg bg-amber-100/80 text-amber-700 flex items-center justify-center shrink-0">
                                            @if ($doc->isImage())
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-900 truncate">{{ $doc->document_type->label() }}</p>
                                            <p class="text-[11px] text-gray-400 font-mono truncate max-w-[140px]">{{ $doc->fileName() }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <x-badge :color="$doc->status->badgeColor()">{{ $doc->status->label() }}</x-badge>
                                        @if ($docDownloadUrl !== '#')
                                            <a href="{{ $docDownloadUrl }}" target="_blank" class="p-1.5 text-gray-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition" title="View Document File">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">No verification documents uploaded yet.</p>
                    @endif
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
            const locationInput = document.getElementById('seller_location_search') || document.getElementById('location_search') || document.getElementById('seller_address_input') || document.getElementById('location') || document.getElementById('address');
            const latitudeInput = document.getElementById('seller_latitude_input') || document.getElementById('latitude');
            const longitudeInput = document.getElementById('seller_longitude_input') || document.getElementById('longitude');

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

                const initialLat = latitudeInput ? parseFloat(latitudeInput.value) : NaN;
                const initialLng = longitudeInput ? parseFloat(longitudeInput.value) : NaN;
                const hasInitialCoords = !isNaN(initialLat) && !isNaN(initialLng);
                const initialCenter = hasInitialCoords ? { lat: initialLat, lng: initialLng } : defaultCenter;

                map = new google.maps.Map(mapCanvas, {
                    center: initialCenter,
                    zoom: hasInitialCoords ? 14 : 12,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                });

                marker = new google.maps.Marker({
                    map: map,
                    position: initialCenter,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                    title: 'Store Location'
                });

                if (!hasInitialCoords) {
                    const initialLoc = locationInput ? locationInput.value : '';
                    if (initialLoc) {
                        geocodeAddress(initialLoc);
                    }
                }

                marker.addListener('dragend', function (e) {
                    geocodePosition(e.latLng);
                    updateCoordinateInputs(e.latLng);
                });

                map.addListener('click', function (e) {
                    marker.setPosition(e.latLng);
                    geocodePosition(e.latLng);
                    updateCoordinateInputs(e.latLng);
                });

                if (latitudeInput) {
                    latitudeInput.addEventListener('change', updateMarkerFromCoordinateInputs);
                }
                if (longitudeInput) {
                    longitudeInput.addEventListener('change', updateMarkerFromCoordinateInputs);
                }
            }

            function updateCoordinateInputs(pos) {
                if (latitudeInput) latitudeInput.value = pos.lat().toFixed(7);
                if (longitudeInput) longitudeInput.value = pos.lng().toFixed(7);
            }

            function updateMarkerFromCoordinateInputs() {
                if (!map || !marker || !latitudeInput || !longitudeInput) return;
                const lat = parseFloat(latitudeInput.value);
                const lng = parseFloat(longitudeInput.value);
                if (isNaN(lat) || isNaN(lng)) return;
                const pos = { lat, lng };
                marker.setPosition(pos);
                map.setCenter(pos);
            }

            function geocodeAddress(addressStr) {
                if (!geocoder || !addressStr) return;
                geocoder.geocode({ address: addressStr }, function (results, status) {
                    if (status === 'OK' && results[0] && map && marker) {
                        const loc = results[0].geometry.location;
                        map.setCenter(loc);
                        map.setZoom(14);
                        marker.setPosition(loc);
                        updateCoordinateInputs(loc);
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
                        updateCoordinateInputs(place.geometry.location);
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

    {{-- Business Hours: "Apply to All Days" --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const applyAllBtn = document.getElementById('bh_apply_all_btn');
            const setAllOpen = document.getElementById('bh_set_all_open');
            const setAllClose = document.getElementById('bh_set_all_close');

            if (!applyAllBtn || !setAllOpen || !setAllClose) return;

            applyAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.js-bh-row').forEach(function (row) {
                    const enabled = row.querySelector('.js-bh-enabled');
                    const open = row.querySelector('.js-bh-open');
                    const close = row.querySelector('.js-bh-close');

                    if (enabled) enabled.checked = true;
                    if (open) open.value = setAllOpen.value;
                    if (close) close.value = setAllClose.value;
                });
            });
        });
    </script>

    {{-- Social Media Links: dynamic add/remove rows with unique platform constraint --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('social_links_list');
            const addBtn = document.getElementById('social_add_link_btn');
            const emptyNotice = document.getElementById('social_links_empty');
            if (!list || !addBtn) return;

            const platforms = @json($socialPlatformOptions);
            const initialLinks = @json($socialLinksList);

            let rowIndex = 0;

            function iconFor(slug) {
                const platform = platforms.find(function (p) { return p.slug === slug; });
                return platform && platform.icon_svg ? platform.icon_svg : '<span class="text-gray-300 text-xs">?</span>';
            }

            function getSelectedPlatforms() {
                const selects = list.querySelectorAll('.js-social-platform');
                const selected = [];
                selects.forEach(function (s) {
                    if (s.value) selected.push(s.value);
                });
                return selected;
            }

            function updateOptionStates() {
                const selects = list.querySelectorAll('.js-social-platform');
                const selectedPlatforms = getSelectedPlatforms();

                selects.forEach(function (select) {
                    const currentValue = select.value;
                    Array.from(select.options).forEach(function (option) {
                        if (option.value === currentValue) {
                            option.disabled = false;
                        } else if (selectedPlatforms.includes(option.value)) {
                            option.disabled = true;
                        } else {
                            option.disabled = false;
                        }
                    });
                });

                if (emptyNotice) {
                    emptyNotice.classList.toggle('hidden', list.children.length > 0);
                }

                if (addBtn) {
                    const allUsed = selectedPlatforms.length >= platforms.length && platforms.length > 0;
                    addBtn.disabled = allUsed;
                    addBtn.classList.toggle('opacity-50', allUsed);
                    addBtn.classList.toggle('cursor-not-allowed', allUsed);
                    addBtn.classList.toggle('cursor-pointer', !allUsed);
                }
            }

            function addRow(requestedPlatformSlug, url) {
                const selectedPlatforms = getSelectedPlatforms();

                let targetPlatform = requestedPlatformSlug;
                if (!targetPlatform || selectedPlatforms.includes(targetPlatform)) {
                    const available = platforms.find(function (p) { return !selectedPlatforms.includes(p.slug); });
                    if (!available) {
                        return; // All platforms already added
                    }
                    targetPlatform = available.slug;
                }

                const index = rowIndex++;

                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 js-social-row';

                const select = document.createElement('select');
                select.name = 'social_links[' + index + '][platform]';
                select.className = 'js-social-platform border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 w-40 shrink-0';
                platforms.forEach(function (p) {
                    const option = document.createElement('option');
                    option.value = p.slug;
                    option.textContent = p.name;
                    if (p.slug === targetPlatform) option.selected = true;
                    select.appendChild(option);
                });

                const iconBadge = document.createElement('span');
                iconBadge.className = 'js-social-icon w-9 h-9 rounded-md border border-gray-200 bg-gray-50 flex items-center justify-center shrink-0';
                iconBadge.innerHTML = iconFor(select.value);

                const urlInput = document.createElement('input');
                urlInput.type = 'text';
                urlInput.name = 'social_links[' + index + '][url]';
                urlInput.value = url || '';
                urlInput.placeholder = 'https://...';
                urlInput.className = 'flex-1 border border-gray-200 rounded-md px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.title = 'Remove';
                removeBtn.className = 'w-9 h-9 rounded-md border border-red-200 text-red-600 hover:bg-red-50 flex items-center justify-center shrink-0 cursor-pointer';
                removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';

                select.addEventListener('change', function () {
                    iconBadge.innerHTML = iconFor(select.value);
                    updateOptionStates();
                });

                removeBtn.addEventListener('click', function () {
                    row.remove();
                    updateOptionStates();
                });

                row.appendChild(select);
                row.appendChild(iconBadge);
                row.appendChild(urlInput);
                row.appendChild(removeBtn);
                list.appendChild(row);

                updateOptionStates();
            }

            addBtn.addEventListener('click', function () {
                addRow('', '');
            });

            initialLinks.forEach(function (link) {
                addRow(link.platform, link.url);
            });

            updateOptionStates();
        });
    </script>

@endsection
