@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.' : 'saler.';
    $storeUrl = ($profile && !empty($profile->slug))
        ? ($isBusiness ? route('stores.business', $profile->slug) : route('stores.saler', $profile->slug))
        : '#';
    $socialLinksList = \App\Support\SocialLinkNormalizer::normalizeForDisplay(old('social_links', $profile->social_links ?? []));
    $currentLogo = $profile->logo ?? $profile->profile_photo ?? null;
    $currentCover = $profile->cover_image ?? null;
    $currentAvatar = $user->avatar ?? $userProfile?->avatar ?? null;
    $isKycApproved = $user->isKycApproved();
    $kycStatusLabel = $isKycApproved ? 'Verified Merchant' : ($kycApplication?->status?->label() ?? 'Pending KYC');
    $activePlan = $activeSubscription?->plan;
@endphp

@extends($isBusiness ? 'layouts.business' : 'layouts.saler')

@section('title', $isBusiness ? 'Store & Settings' : 'Store & Settings')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @if ($errors->any())
        <div class="p-4 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Please fix the errors below:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. HERO PROFILE BANNER (Matching Mobile Seller Header) --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden mb-6">
        <div class="relative h-44 sm:h-52 bg-slate-900 overflow-hidden">
            @if ($currentCover)
                <img src="{{ \App\Support\MediaUrl::resolve($currentCover) }}" alt="Cover Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
            @else
                <div class="w-full h-full bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 flex items-center justify-center opacity-90">
                    <div class="text-white/20 text-center select-none">
                        <svg class="w-16 h-16 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
                        <span class="text-xs uppercase tracking-widest font-semibold">Store Cover Banner</span>
                    </div>
                </div>
            @endif

            <div class="absolute top-4 right-4 flex items-center gap-2">
                @if ($storeUrl !== '#')
                    <a href="{{ $storeUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-black/40 hover:bg-black/60 backdrop-blur-md rounded-lg border border-white/20 transition">
                        <span>View Live Store</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </div>
        </div>

        <div class="px-6 pb-6 pt-0">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-14 mb-4">
                <div class="flex items-end gap-4">
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white p-1 shadow-md border-2 border-white shrink-0 overflow-hidden">
                        @if ($currentLogo)
                            <img src="{{ \App\Support\MediaUrl::resolve($currentLogo) }}" alt="Store Logo" class="w-full h-full object-contain rounded-xl">
                        @elseif ($currentAvatar)
                            <img src="{{ \App\Support\MediaUrl::resolve($currentAvatar) }}" alt="Avatar" class="w-full h-full object-cover object-top rounded-xl">
                        @else
                            <div class="w-full h-full bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 font-black text-2xl uppercase">
                                {{ substr($isBusiness ? ($profile->business_name ?? $user->name) : ($profile->display_name ?? $user->name), 0, 2) }}
                            </div>
                        @endif
                    </div>

                    <div class="pt-2">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                                {{ $isBusiness ? ($profile->business_name ?? $user->name) : ($profile->display_name ?? $user->name) }}
                            </h1>
                            @if ($isKycApproved)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $kycStatusLabel }}
                                </span>
                            @endif

                            @if ($profile->is_store_active ?? true)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Public
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Private / Closed
                                </span>
                            @endif
                        </div>

                        <p class="text-xs sm:text-sm text-gray-500 font-medium mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span>Owner: <strong class="text-gray-700">{{ $user->name }}</strong></span>
                            <span>•</span>
                            <span>{{ $user->email }}</span>
                            @if ($user->phone)
                                <span>•</span>
                                <span>{{ $user->phone }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Quick Stat Badges --}}
                <div class="flex items-center gap-2 shrink-0 pt-2 sm:pt-0">
                    <div class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-center min-w-[90px]">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Products</span>
                        <span class="text-sm font-extrabold text-gray-800">{{ $productsCount ?? 0 }}</span>
                    </div>
                    <div class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-center min-w-[100px]">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Plan</span>
                        <span class="text-sm font-extrabold text-amber-700 truncate block max-w-[120px]">{{ $activePlan?->name ?? 'Free Tier' }}</span>
                    </div>
                </div>
            </div>

            {{-- 2. TAB NAVIGATION (Matching Mobile Seller Sub-Screens) --}}
            <div class="border-t border-gray-100 pt-4 mt-2">
                <nav class="flex space-x-1 sm:space-x-2 overflow-x-auto no-scrollbar" id="settings-tab-bar" role="tablist">
                    <button type="button" class="js-tab-btn active inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl transition whitespace-nowrap cursor-pointer text-amber-800 bg-amber-50 border border-amber-200" data-target="#tab-store">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Store Settings
                    </button>
                    <button type="button" class="js-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition whitespace-nowrap cursor-pointer border border-transparent" data-target="#tab-profile">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Account Profile
                    </button>
                    <button type="button" class="js-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition whitespace-nowrap cursor-pointer border border-transparent" data-target="#tab-kyc">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Verification & KYC
                        @if ($isKycApproved)
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        @endif
                    </button>
                    <button type="button" class="js-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition whitespace-nowrap cursor-pointer border border-transparent" data-target="#tab-payment">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Payment & Payouts
                    </button>
                    <button type="button" class="js-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition whitespace-nowrap cursor-pointer border border-transparent" data-target="#tab-subscription">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Subscription & Portal
                    </button>
                </nav>
            </div>
        </div>
    </div>

    {{-- MAIN EDIT FORM --}}
    <form method="POST" action="{{ route($routePrefix.'store.update') }}" enctype="multipart/form-data" id="seller_settings_form">
        @csrf

        {{-- ======================================================== --}}
        {{-- TAB 1: STORE SETTINGS (Store Name, Logo, Address, Hours)  --}}
        {{-- ======================================================== --}}
        <div id="tab-store" class="js-tab-content space-y-6">
            <x-card>
                <x-slot:title>Store Identity & Branding</x-slot:title>
                <x-slot:action>
                    <span class="text-xs text-gray-400">Visible on public store page</span>
                </x-slot:action>

                <div class="space-y-6">
                    @if ($isBusiness)
                        <div class="grid sm:grid-cols-2 gap-5">
                            <x-input label="Business / Store Name" name="business_name" type="text" :value="old('business_name', $profile->business_name)" required />
                            <x-input label="Store Handle / Slug" name="slug" type="text" :value="old('slug', $profile->slug)" hint="Used in your public store link" />
                        </div>
                        <x-textarea label="About Store / Description" name="description" rows="3" :value="old('description', $profile->description)" hint="Introduce your business, products and offerings to buyers." />
                    @else
                        <div class="grid sm:grid-cols-2 gap-5">
                            <x-input label="Display / Store Name" name="display_name" type="text" :value="old('display_name', $profile->display_name)" required />
                            <x-input label="Seller Handle / Slug" name="slug" type="text" :value="old('slug', $profile->slug)" hint="Used in your public profile link" />
                        </div>
                        <x-textarea label="Bio / Store Bio" name="bio" rows="3" :value="old('bio', $profile->bio)" hint="Tell customers what you specialize in and what you sell." />
                    @endif

                    <div class="grid sm:grid-cols-2 gap-5 pt-2 border-t border-gray-100">
                        <div class="max-w-[240px]">
                            <x-file-upload
                                :name="$isBusiness ? 'logo' : 'profile_photo'"
                                label="Store Logo"
                                hint="Recommended 400×400 square image"
                                :value="\App\Support\MediaUrl::resolve($currentLogo)"
                                fill
                            />
                        </div>
                        <div>
                            <x-file-upload
                                name="cover_image"
                                label="Cover Photo / Store Banner"
                                hint="Recommended 1200×600 banner"
                                :value="\App\Support\MediaUrl::resolve($currentCover)"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot:title>Store Location & Interactive Map</x-slot:title>
                <x-slot:action>
                    <span class="text-xs text-gray-400">Helps buyers find nearby sellers</span>
                </x-slot:action>

                <div class="space-y-6">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input label="Search Google Places" name="location_search" type="text" id="seller_location_search" placeholder="Type an address, city, or place..." value="" />
                        </div>

                        @if ($isBusiness)
                            <x-input label="Store Address" name="address" type="text" id="seller_address_input" placeholder="Building, street, block..." :value="old('address', $profile->address)" />
                        @else
                            <x-input label="Store Location" name="location" type="text" id="seller_location_input" placeholder="Seller area, street, landmark..." :value="old('location', $profile->location)" />
                        @endif

                        <x-select label="Country" name="country" id="seller_country_select" placeholder="Select Country" :selected="old('country', $profile->country)">
                            @if ($profile->country)
                                <option value="{{ $profile->country }}" selected>{{ $profile->country }}</option>
                            @endif
                        </x-select>
                        <x-select label="City" name="city" id="seller_city_select" placeholder="Select City" :selected="old('city', $profile->city)">
                            @if ($profile->city)
                                <option value="{{ $profile->city }}" selected>{{ $profile->city }}</option>
                            @endif
                        </x-select>

                        <x-input label="Latitude" name="latitude" type="number" step="any" min="-90" max="90" id="seller_latitude_input" placeholder="e.g. 23.8103" :value="old('latitude', $profile->latitude)" />
                        <x-input label="Longitude" name="longitude" type="number" step="any" min="-180" max="180" id="seller_longitude_input" placeholder="e.g. 90.4125" :value="old('longitude', $profile->longitude)" />

                        @if ($isBusiness)
                            <x-input label="Store Phone" name="phone" type="tel" :value="old('phone', $profile->phone)" />
                            <x-input label="Website URL" name="website" type="text" placeholder="https://yourstore.com" :value="old('website', $profile->website ?? '')" />
                        @endif
                    </div>

                    {{-- Interactive Map --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Pin Location on Map</label>
                            <span class="text-xs text-gray-400">Click or drag the pin to set exact coordinates</span>
                        </div>
                        <div id="seller_map_canvas" class="w-full h-72 sm:h-80 rounded-xl border border-gray-200 shadow-2xs overflow-hidden bg-gray-100"></div>
                    </div>
                </div>
            </x-card>

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
            <x-card>
                <x-slot:title>Business Operating Hours</x-slot:title>
                <x-slot:action>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">Bulk:</span>
                        <input type="time" id="bh_set_all_open" value="08:00" class="border border-gray-200 rounded-md px-2 py-1 text-xs text-gray-700">
                        <span class="text-gray-300">–</span>
                        <input type="time" id="bh_set_all_close" value="17:00" class="border border-gray-200 rounded-md px-2 py-1 text-xs text-gray-700">
                        <button type="button" id="bh_apply_all_btn" class="px-2.5 py-1 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-md transition cursor-pointer">
                            Apply All
                        </button>
                    </div>
                </x-slot:action>

                <div class="space-y-4">
                    <div class="max-w-xs">
                        <x-select label="Operating Timezone" name="timezone" :options="$timezoneOptions" :selected="old('timezone', $profile->timezone ?? config('app.timezone'))" />
                    </div>

                    <div class="border border-gray-200 rounded-xl divide-y divide-gray-100 overflow-hidden bg-white">
                        @foreach ($businessHours as $day => $hours)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 js-bh-row hover:bg-gray-50/50 transition">
                                <label class="flex items-center gap-2.5 text-sm font-semibold text-gray-700 min-w-[130px] cursor-pointer">
                                    <input type="hidden" name="business_hours[{{ $day }}][enabled]" value="0">
                                    <input type="checkbox" name="business_hours[{{ $day }}][enabled]" value="1" @checked($hours['enabled']) class="js-bh-enabled w-4 h-4 rounded accent-amber-500">
                                    {{ ucfirst($day) }}
                                </label>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-xs text-gray-400">Open:</span>
                                    <input type="time" name="business_hours[{{ $day }}][open]" value="{{ $hours['open'] }}" class="js-bh-open border border-gray-200 rounded-md px-2.5 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    <span class="text-gray-300">–</span>
                                    <span class="text-xs text-gray-400">Close:</span>
                                    <input type="time" name="business_hours[{{ $day }}][close]" value="{{ $hours['close'] }}" class="js-bh-close border border-gray-200 rounded-md px-2.5 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-card>

            {{-- Social Media Links --}}
            <x-card>
                <x-slot:title>Social Media Profiles</x-slot:title>
                <x-slot:action>
                    <button type="button" id="social_add_link_btn" class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 hover:text-amber-800 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Social Link
                    </button>
                </x-slot:action>

                <div id="social_links_list" class="space-y-2.5"></div>
                <p id="social_links_empty" class="text-xs text-gray-400 italic hidden">No social media links added yet.</p>
            </x-card>

            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-2xl">
                <label class="flex items-center gap-3 text-sm font-semibold text-gray-700 cursor-pointer">
                    <input type="checkbox" name="is_store_active" value="1" @checked(old('is_store_active', $profile->is_store_active ?? true)) class="w-4 h-4 rounded accent-amber-500">
                    <div>
                        <span>Public Store Visibility</span>
                        <span class="block text-xs font-normal text-gray-400">When checked, customers can browse your store and view products</span>
                    </div>
                </label>

                <x-button type="submit">Save Store Settings</x-button>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 2: ACCOUNT PROFILE (Owner Info, Choose Photo, Company) --}}
        {{-- ======================================================== --}}
        <div id="tab-profile" class="js-tab-content space-y-6 hidden">
            <x-card>
                <x-slot:title>Account Owner Profile</x-slot:title>
                <x-slot:action>
                    <span class="text-xs text-gray-400">Personal owner credentials</span>
                </x-slot:action>

                <div class="space-y-6">
                    {{-- Choose Photo (Avatar) --}}
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 p-5 bg-gray-50/70 border border-gray-200/80 rounded-2xl">
                        <div class="relative w-24 h-24 rounded-full bg-white p-1 shadow-sm border border-gray-200 shrink-0 overflow-hidden">
                            @if ($currentAvatar)
                                <img src="{{ \App\Support\MediaUrl::resolve($currentAvatar) }}" id="avatar_preview" alt="Owner Avatar" class="w-full h-full object-cover object-top rounded-full">
                            @else
                                <div id="avatar_placeholder" class="w-full h-full bg-amber-100/80 rounded-full flex items-center justify-center text-amber-700 font-extrabold text-2xl uppercase">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <img src="" id="avatar_preview" alt="Owner Avatar" class="w-full h-full object-cover object-top rounded-full hidden">
                            @endif
                        </div>

                        <div class="space-y-1.5 text-center sm:text-left">
                            <h4 class="text-sm font-bold text-gray-800">Profile Photo / Avatar</h4>
                            <p class="text-xs text-gray-500">Upload a clear square portrait of yourself or your merchant representative (JPG, PNG max 5MB).</p>
                            <div class="pt-2">
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 bg-white hover:bg-amber-50 rounded-lg border border-amber-300 transition cursor-pointer shadow-2xs">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Choose Photo</span>
                                    <input type="file" name="avatar" id="avatar_file_input" accept="image/*" class="hidden">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <x-input label="Full Name" name="name" type="text" :value="old('name', $user->name)" placeholder="Your legal full name" required />
                        <x-input label="Company / Trading Name" name="company" type="text" :value="old('company', $userProfile?->company ?? ($isBusiness ? $profile->business_name : ''))" placeholder="e.g. Al-Mansoor Trading LLC" />
                        <x-input label="Operating Location / Area" name="location" type="text" :value="old('location', $userProfile?->location ?? ($isBusiness ? $profile->address : $profile->location))" placeholder="e.g. Doha Industrial Zone, Qatar" />
                        <x-input label="Phone Number" name="phone" type="tel" :value="old('phone', $user->phone)" placeholder="e.g. +974 5555 1234" />
                    </div>

                    <div class="p-4 bg-gray-50 border border-gray-200/80 rounded-xl space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Registered Email Address</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Email Verified
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-gray-800">{{ $user->email }}</p>
                        <p class="text-[11px] text-gray-400">Account login email cannot be changed directly from store settings.</p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-button type="submit">Save Profile Details</x-button>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 3: VERIFICATION & KYC (Compliance & Documents)       --}}
        {{-- ======================================================== --}}
        <div id="tab-kyc" class="js-tab-content space-y-6 hidden">
            <x-card>
                <x-slot:title>KYC Identity & Business Verification</x-slot:title>
                <x-slot:action>
                    @if (Route::has($routePrefix . 'verification.index'))
                        <a href="{{ route($routePrefix . 'verification.index') }}" class="text-xs font-bold text-amber-700 hover:underline">Full Verification Portal →</a>
                    @endif
                </x-slot:action>

                <div class="space-y-6">
                    {{-- Status Banner --}}
                    <div class="p-5 rounded-2xl border {{ $isKycApproved ? 'bg-emerald-50/70 border-emerald-200' : 'bg-amber-50/70 border-amber-200' }} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl {{ $isKycApproved ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }} flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900">
                                    {{ $isKycApproved ? 'Identity & Store Fully Verified' : ($kycApplication ? 'Verification Under Compliance Review' : 'Identity Verification Pending') }}
                                </h3>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    {{ $isKycApproved ? 'Your merchant credentials have been authenticated. You enjoy maximum listing priority and payout authorization.' : 'Upload required identity documents to achieve verified merchant status and enable direct payouts.' }}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $isKycApproved ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                {{ $kycStatusLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- Requirements Checklist --}}
                    @if (isset($kycRequirements) && count($kycRequirements))
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Compliance Checklist</h4>
                            <div class="grid sm:grid-cols-2 gap-3">
                                @foreach ($kycRequirements as $req)
                                    <div class="p-3 bg-gray-50 border border-gray-200/80 rounded-xl flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 {{ $req->is_mandatory ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="font-bold text-gray-800">{{ $req->name }}</span>
                                        </div>
                                        <span class="text-[10px] font-semibold {{ $req->is_mandatory ? 'text-amber-700 bg-amber-100/70' : 'text-gray-600 bg-gray-200' }} px-2 py-0.5 rounded-full">
                                            {{ $req->is_mandatory ? 'Required' : 'Optional' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Submitted Documents --}}
                    @php
                        $submittedDocs = $kycApplication?->documents ?? collect();
                    @endphp
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Submitted Documents</h4>
                            <span class="text-xs text-gray-400">{{ $submittedDocs->count() }} file(s) attached</span>
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
                                                <p class="text-xs font-bold text-gray-900 truncate">{{ $doc->document_type?->label() ?? 'Document' }}</p>
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
                            <div class="p-6 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-center">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-xs text-gray-600 font-semibold">No verification documents uploaded yet.</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Submit your National ID / Passport in the verification center to begin review.</p>
                                @if (Route::has($routePrefix . 'verification.index'))
                                    <div class="mt-3">
                                        <a href="{{ route($routePrefix . 'verification.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-lg transition">
                                            Upload Documents Now →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 4: PAYMENT & PAYOUT SETTINGS (Bank, IBAN, Gateways)   --}}
        {{-- ======================================================== --}}
        <div id="tab-payment" class="js-tab-content space-y-6 hidden">
            <x-card>
                <x-slot:title>Bank Account for Payouts</x-slot:title>
                <x-slot:action>
                    <span class="text-xs text-gray-400">Used for merchant balance withdrawals</span>
                </x-slot:action>

                <div class="space-y-6">
                    <div class="p-4 bg-amber-50/60 border border-amber-200/80 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="text-xs text-amber-900 leading-relaxed">
                            <strong>Official Bank Details:</strong> Funds earned through customer orders will be automatically or manually transferred to this bank account upon request.
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <x-input label="Bank Name" name="bank_name" type="text" placeholder="e.g. Qatar National Bank (QNB)" :value="old('bank_name', $paymentSettings->bank_name ?? '')" />
                        <x-input label="Account Holder Name" name="account_name" type="text" placeholder="e.g. John Doe / My Store Trading" :value="old('account_name', $paymentSettings->account_name ?? '')" />
                        <x-input label="Account Number" name="account_number" type="text" placeholder="e.g. 00123456789" :value="old('account_number', $paymentSettings->account_number ?? '')" />
                        <x-input label="IBAN Number" name="iban" type="text" placeholder="e.g. QA58QNBA00000000123456" :value="old('iban', $paymentSettings->iban ?? '')" />
                        <x-input label="SWIFT / BIC Code" name="swift_code" type="text" placeholder="e.g. QNBAQAQA" :value="old('swift_code', $paymentSettings->swift_code ?? '')" />
                        <x-input label="Bank Branch" name="bank_branch" type="text" placeholder="e.g. Main Branch, Doha" :value="old('bank_branch', $paymentSettings->bank_branch ?? '')" />
                    </div>

                    <div class="border-t border-gray-100 pt-5 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Order Payment Methods Allowed</h4>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="flex items-start gap-3 p-3.5 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100/60 transition">
                                <input type="hidden" name="manual_bank_enabled" value="0">
                                <input type="checkbox" name="manual_bank_enabled" value="1" @checked(old('manual_bank_enabled', $paymentSettings->manual_bank_enabled ?? true)) class="w-4 h-4 rounded accent-amber-500 mt-0.5">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-900 block">Direct Bank Transfer</span>
                                    <span class="text-gray-500">Allow customers to submit proof of bank wire transfer for orders.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3.5 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100/60 transition">
                                <input type="hidden" name="cod_enabled" value="0">
                                <input type="checkbox" name="cod_enabled" value="1" @checked(old('cod_enabled', $paymentSettings->cod_enabled ?? true)) class="w-4 h-4 rounded accent-amber-500 mt-0.5">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-900 block">Cash on Delivery (COD)</span>
                                    <span class="text-gray-500">Allow customers to pay in cash upon receiving goods.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-button type="submit">Save Payment Settings</x-button>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 5: SUBSCRIPTION & LOGISTICS (Tiers, Portal, Shortcuts) --}}
        {{-- ======================================================== --}}
        <div id="tab-subscription" class="js-tab-content space-y-6 hidden">
            {{-- Active Plan Hero Card (Matching Mobile SubscriptionPlanScreen) --}}
            <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-400/30">
                                Current Plan
                            </span>
                            <span class="text-xs text-slate-400">
                                Status: <strong class="text-emerald-400">{{ $activeSubscription ? 'Active' : 'Free Trial' }}</strong>
                            </span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            {{ $activePlan?->name ?? 'Merchant Starter Plan' }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 max-w-md">
                            {{ $activePlan?->description ?? 'Full seller dashboard access, standard commission rates, and product listings.' }}
                        </p>
                    </div>

                    <div class="shrink-0 flex flex-col items-start sm:items-end gap-3">
                        <div class="text-left sm:text-right">
                            <span class="text-xs text-slate-400 uppercase tracking-widest block font-semibold">Listing Credits</span>
                            <span class="text-2xl font-black text-amber-400">
                                {{ $activeSubscription?->credits_remaining ?? 'Unlimited' }}
                            </span>
                        </div>

                        @php
                            $subRoute = $isBusiness ? 'business.subscription.index' : 'saler.subscriptions.index';
                        @endphp
                        @if (Route::has($subRoute))
                            <a href="{{ route($subRoute) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-slate-900 bg-amber-400 hover:bg-amber-300 rounded-xl transition shadow-xs">
                                <span>Manage Subscription</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Operational Shortcuts (Matching Mobile Seller Navigation) --}}
            <x-card>
                <x-slot:title>Merchant Operations & Fulfillment Shortcuts</x-slot:title>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route($routePrefix . 'orders.index') }}" class="p-4 bg-gray-50 hover:bg-amber-50/60 border border-gray-200/80 rounded-xl transition group flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-amber-800">Orders & Fulfillment</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Manage customer orders, track dispatches, and print shipping slips.</p>
                        </div>
                    </a>

                    @if (!$isBusiness && Route::has('saler.appointments.index'))
                        <a href="{{ route('saler.appointments.index') }}" class="p-4 bg-gray-50 hover:bg-amber-50/60 border border-gray-200/80 rounded-xl transition group flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900 group-hover:text-amber-800">Inspection Appointments</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">Product quality verifications and verifier station slots.</p>
                            </div>
                        </a>
                    @endif

                    @if (!$isBusiness && Route::has('saler.warehouse.index'))
                        <a href="{{ route('saler.warehouse.index') }}" class="p-4 bg-gray-50 hover:bg-amber-50/60 border border-gray-200/80 rounded-xl transition group flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900 group-hover:text-amber-800">Warehouse Consignment</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">Deposit items at central hubs for automated packing.</p>
                            </div>
                        </a>
                    @endif

                    <a href="{{ route($routePrefix . 'messages.index') }}" class="p-4 bg-gray-50 hover:bg-amber-50/60 border border-gray-200/80 rounded-xl transition group flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-amber-800">Customer Messages</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Real-time buyer chats and order inquiries.</p>
                        </div>
                    </a>

                    <a href="{{ route($routePrefix . 'support.index') }}" class="p-4 bg-gray-50 hover:bg-amber-50/60 border border-gray-200/80 rounded-xl transition group flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m-5.656 5.656l-3.536 3.536m12.728 0l-3.536-3.536M9.172 9.172L5.636 5.636M12 12a3 3 0 100-6 3 3 0 000 6zm0 0a3 3 0 110 6 3 3 0 010-6z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-amber-800">Support Tickets</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">Open queries with admin operations team.</p>
                        </div>
                    </a>
                </div>
            </x-card>
        </div>

    </form>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper {
            border: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .ts-control {
            border-radius: 0.5rem !important;
            border-color: #e2e8f0 !important;
            padding: 0.625rem 0.875rem 0.625rem 2.25rem !important;
            font-size: 0.875rem !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: 0.75rem center !important;
            background-size: 1rem 1rem !important;
        }
        .ts-control > input {
            margin-left: 0 !important;
        }
        .ts-control.focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.4) !important;
        }
        .ts-dropdown {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e2e8f0 !important;
            margin-top: 4px !important;
            z-index: 50 !important;
        }
        .ts-dropdown .option.active, .ts-dropdown .option:hover {
            background-color: #fffbeb !important;
            color: #d97706 !important;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{-- TAB SWITCHER & AVATAR PREVIEW (Runs immediately without waiting on external CDNs) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tab Switcher
            const tabButtons = document.querySelectorAll('.js-tab-btn');
            const tabContents = document.querySelectorAll('.js-tab-content');

            function activateTab(targetId) {
                tabButtons.forEach(btn => {
                    const isCurrent = btn.getAttribute('data-target') === targetId;
                    btn.classList.toggle('active', isCurrent);
                    btn.classList.toggle('text-amber-800', isCurrent);
                    btn.classList.toggle('bg-amber-50', isCurrent);
                    btn.classList.toggle('border-amber-200', isCurrent);
                    btn.classList.toggle('text-gray-600', !isCurrent);
                    btn.classList.toggle('border-transparent', !isCurrent);
                });

                tabContents.forEach(content => {
                    const matches = '#' + content.id === targetId;
                    content.classList.toggle('hidden', !matches);
                });

                if (targetId === '#tab-store' && typeof google !== 'undefined' && google.maps && window.sellerMapInstance) {
                    google.maps.event.trigger(window.sellerMapInstance, 'resize');
                    if (window.sellerMarkerInstance) {
                        window.sellerMapInstance.setCenter(window.sellerMarkerInstance.getPosition());
                    }
                }
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const target = this.getAttribute('data-target');
                    activateTab(target);
                    if (history.replaceState) {
                        history.replaceState(null, null, target);
                    }
                });
            });

            // Read hash on load
            if (window.location.hash) {
                const initialHash = window.location.hash;
                if (document.querySelector('.js-tab-btn[data-target="' + initialHash + '"]')) {
                    activateTab(initialHash);
                }
            }

            // Avatar Live Preview
            const avatarInput = document.getElementById('avatar_file_input');
            const avatarPreview = document.getElementById('avatar_preview');
            const avatarPlaceholder = document.getElementById('avatar_placeholder');

            if (avatarInput) {
                avatarInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            if (avatarPreview) {
                                avatarPreview.src = e.target.result;
                                avatarPreview.classList.remove('hidden');
                            }
                            if (avatarPlaceholder) {
                                avatarPlaceholder.classList.add('hidden');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCsdLSxCzJS1DypOOyGan4BWTZvZIhiS9M&libraries=places"></script>

    {{-- GOOGLE MAPS & LOCATION AUTOCOMPLETE --}}
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
                    const response = await fetch('{{ asset('data/countries-cities.json') }}');
                    countriesData = await response.json();
                } catch (err) {
                    console.warn('Unable to load countries/cities dataset:', err);
                }

                initTomSelectInstances();
                populateCountries(initialCountry);
                if (initialCountry) {
                    populateCities(initialCountry, initialCity);
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

                    countryTomSelect.refreshOptions(false);
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

                    cityTomSelect.refreshOptions(false);
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
                window.sellerMapInstance = map;

                marker = new google.maps.Marker({
                    map: map,
                    position: initialCenter,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                    title: 'Store Location'
                });
                window.sellerMarkerInstance = marker;

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

    {{-- Business Hours: Apply All --}}
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

    {{-- Social Media Links: dynamic add/remove --}}
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
                        return;
                    }
                    targetPlatform = available.slug;
                }

                const index = rowIndex++;

                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 js-social-row';

                const select = document.createElement('select');
                select.name = 'social_links[' + index + '][platform]';
                select.className = 'js-social-platform border border-gray-200 rounded-md px-3 py-2 text-xs text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 w-40 shrink-0';
                platforms.forEach(function (p) {
                    const option = document.createElement('option');
                    option.value = p.slug;
                    option.textContent = p.name;
                    if (p.slug === targetPlatform) option.selected = true;
                    select.appendChild(option);
                });

                const iconBadge = document.createElement('span');
                iconBadge.className = 'js-social-icon w-8 h-8 rounded-md border border-gray-200 bg-gray-50 flex items-center justify-center shrink-0';
                iconBadge.innerHTML = iconFor(select.value);

                const urlInput = document.createElement('input');
                urlInput.type = 'text';
                urlInput.name = 'social_links[' + index + '][url]';
                urlInput.value = url || '';
                urlInput.placeholder = 'https://...';
                urlInput.className = 'flex-1 border border-gray-200 rounded-md px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-400';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.title = 'Remove';
                removeBtn.className = 'w-8 h-8 rounded-md border border-red-200 text-red-600 hover:bg-red-50 flex items-center justify-center shrink-0 cursor-pointer';
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
