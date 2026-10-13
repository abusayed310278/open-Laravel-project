@extends('layouts.admin')

@section('title', 'Settings — App Links')

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

    <div class="space-y-6">
        {{-- Live Footer Bottom Section Preview Card (Star Tech Style) --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Footer App &amp; Social Bar Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time live preview of how your app download badges, heading, and social links appear in the public footer (Star Tech style).</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Preview
                </span>
            </div>

            {{-- Dark Mockup Container --}}
            <div class="rounded-xl border border-slate-800 shadow-inner overflow-hidden" style="background-color: #080c18;">
                {{-- App & Social Bar --}}
                <div class="p-4 sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4 border-b border-white/10">
                    {{-- Left: Heading + App Badges --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 text-center sm:text-left">
                        <span id="preview_app_heading" class="text-xs sm:text-sm text-gray-400 font-normal select-none whitespace-nowrap">
                            {{ $appHeading ?? ('Experience ' . config('app.name', 'Openbox') . ' App on your mobile:') }}
                        </span>

                        <div id="preview_app_badges" class="flex items-center gap-2.5 flex-wrap justify-center"></div>
                    </div>

                    {{-- Right: Social Icons --}}
                    <div id="preview_social_icons" class="flex items-center gap-2.5 shrink-0"></div>
                </div>

                {{-- Copyright Line --}}
                <div class="px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500" style="background-color: #05070f;">
                    <span>&copy; {{ date('Y') }} {{ config('app.name', 'Openbox') }} Ltd | All rights reserved</span>
                    <span>Powered By: {{ config('app.name', 'Openbox') }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.app-links.update') }}" class="space-y-6">
            @csrf

            {{-- Card 0: App Section Heading --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            App Section Heading Text
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Descriptive label shown directly to the left of the mobile app download badges (as seen in Star Tech).</p>
                    </div>

                    <button
                        type="button"
                        id="reset_app_heading_btn"
                        class="text-xs font-semibold text-amber-700 hover:text-amber-800 hover:underline cursor-pointer"
                    >
                        Reset to Default
                    </button>
                </div>

                <input
                    type="text"
                    name="footer_app_heading"
                    id="footer_app_heading_input"
                    value="{{ old('footer_app_heading', $appHeading ?? ('Experience ' . config('app.name', 'Openbox') . ' App on your mobile:')) }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 mt-2"
                    placeholder="Experience {{ config('app.name', 'Openbox') }} App on your mobile:"
                >
            </div>

            {{-- Card 1: Mobile App Download Links --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6 sm:p-7 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Mobile App Download Links
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Configure your official app download links (Google Play, Apple App Store, Huawei, APK, etc.) shown in the footer bottom bar.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="load_default_apps_btn"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg border border-gray-300 transition-colors cursor-pointer shrink-0"
                        >
                            Default Apps
                        </button>
                        <button
                            type="button"
                            id="app_add_link_btn"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg border border-amber-200 transition-colors cursor-pointer shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add App Link
                        </button>
                    </div>
                </div>

                <div id="app_links_list" class="space-y-3"></div>

                <div id="app_links_empty" class="text-center py-8 px-4 border-2 border-dashed border-gray-100 rounded-xl bg-gray-50/50">
                    <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700">No app links added yet</p>
                    <p class="text-xs text-gray-400 mt-0.5">Click "Add App Link" above to facilitate mobile download buttons in your website footer.</p>
                </div>
            </div>

            {{-- Card 2: Footer Social Media Profiles --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6 sm:p-7 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Footer Social Media Profiles
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Official brand social media links (Facebook, X, Instagram, YouTube, LinkedIn, WhatsApp) shown alongside app badges.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="load_default_socials_btn"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg border border-gray-300 transition-colors cursor-pointer shrink-0"
                        >
                            Default Socials
                        </button>
                        <button
                            type="button"
                            id="social_add_link_btn"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-800 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg border border-indigo-200 transition-colors cursor-pointer shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Social Link
                        </button>
                    </div>
                </div>

                <div id="social_links_list" class="space-y-3"></div>

                <div id="social_links_empty" class="text-center py-6 px-4 border-2 border-dashed border-gray-100 rounded-xl bg-gray-50/50">
                    <p class="text-sm font-medium text-gray-700">No social media links added yet</p>
                    <p class="text-xs text-gray-400 mt-0.5">Click "Add Social Link" or "Default Socials" above to display official social media icons in the footer.</p>
                </div>
            </div>

            {{-- Submit Action Bar --}}
            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl shadow-xs">
                <span class="text-xs text-gray-500">All enabled links and heading will be immediately shown in the public footer (Star Tech style).</span>
                <x-button type="submit" class="shadow-sm px-6 py-2.5">
                    <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save App &amp; Social Links
                </x-button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const appPlatforms = @json($appPlatforms);
            const initialAppLinks = @json($appLinks);

            const socialPlatforms = @json($socialPlatforms);
            const initialSocialLinks = @json($socialLinks);

            // Heading Logic
            const headingInput = document.getElementById('footer_app_heading_input');
            const previewHeading = document.getElementById('preview_app_heading');
            const resetHeadingBtn = document.getElementById('reset_app_heading_btn');

            if (headingInput && previewHeading) {
                headingInput.addEventListener('input', function () {
                    previewHeading.textContent = this.value.trim() || ('Experience ' + @json(config('app.name', 'Openbox')) + ' App on your mobile:');
                });
            }
            if (resetHeadingBtn && headingInput) {
                resetHeadingBtn.addEventListener('click', function () {
                    headingInput.value = 'Experience ' + @json(config('app.name', 'Openbox')) + ' App on your mobile:';
                    headingInput.dispatchEvent(new Event('input'));
                });
            }

            // App Links Logic
            const appList = document.getElementById('app_links_list');
            const appAddBtn = document.getElementById('app_add_link_btn');
            const appEmptyNotice = document.getElementById('app_links_empty');
            const previewAppBadges = document.getElementById('preview_app_badges');
            const loadDefaultAppsBtn = document.getElementById('load_default_apps_btn');

            let appRowIndex = 0;

            function appMeta(slug) {
                return appPlatforms.find(function (p) { return p.slug === slug; }) || appPlatforms[0];
            }

            function updateAppLivePreview() {
                if (!previewAppBadges) return;
                previewAppBadges.innerHTML = '';

                const rows = appList.querySelectorAll('.js-app-row');
                let count = 0;

                rows.forEach(function (row) {
                    const activeCheckbox = row.querySelector('.js-app-active');
                    if (activeCheckbox && !activeCheckbox.checked) return;

                    const select = row.querySelector('.js-app-platform');
                    const urlInput = row.querySelector('.js-app-url');
                    const titleInput = row.querySelector('.js-app-title');
                    const subtitleInput = row.querySelector('.js-app-subtitle');

                    const meta = appMeta(select.value);
                    const title = (titleInput && titleInput.value.trim()) || meta.default_title;
                    const subtitle = (subtitleInput && subtitleInput.value.trim()) || meta.subtitle;
                    const url = (urlInput && urlInput.value.trim()) || '#';

                    const a = document.createElement('a');
                    a.href = url;
                    a.target = '_blank';
                    a.className = 'inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-md bg-black/60 border border-white/20 text-white shadow-xs';
                    a.innerHTML = `
                        <span class="text-white shrink-0">${meta.icon_svg}</span>
                        <span class="flex flex-col text-left leading-none">
                            <span class="text-[9px] text-gray-400 font-normal leading-tight">${subtitle}</span>
                            <span class="text-xs font-bold tracking-tight text-white mt-0.5 leading-tight">${title}</span>
                        </span>
                    `;
                    previewAppBadges.appendChild(a);
                    count++;
                });

                if (count === 0) {
                    previewAppBadges.innerHTML = '<span class="text-[11px] text-gray-500 italic">No app badges active</span>';
                }
            }

            function addAppRow(data) {
                const index = appRowIndex++;
                const currentPlatformSlug = data ? data.platform : (appPlatforms[0]?.slug || 'google-play');
                const meta = appMeta(currentPlatformSlug);

                const row = document.createElement('div');
                row.className = 'js-app-row flex flex-col md:flex-row md:items-center gap-3 p-3.5 bg-gray-50 border border-gray-200 rounded-xl transition-colors hover:bg-gray-50/80';

                // Platform Dropdown with icon badge
                let optionsHtml = '';
                appPlatforms.forEach(function (p) {
                    const sel = p.slug === currentPlatformSlug ? 'selected' : '';
                    optionsHtml += `<option value="${p.slug}" ${sel}>${p.name}</option>`;
                });

                row.innerHTML = `
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="js-app-icon-badge w-9 h-9 rounded-lg border border-gray-200 bg-white flex items-center justify-center shrink-0 shadow-2xs">
                            ${meta.icon_svg}
                        </span>
                        <select name="app_links[${index}][platform]" class="js-app-platform border border-gray-200 rounded-lg px-2.5 py-2 text-xs font-semibold text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shrink-0 w-36">
                            ${optionsHtml}
                        </select>
                    </div>

                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <input
                            type="text"
                            name="app_links[${index}][title]"
                            value="${data && data.title ? data.title : ''}"
                            placeholder="Title (e.g. ${meta.default_title})"
                            class="js-app-title border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                        >
                        <input
                            type="text"
                            name="app_links[${index}][subtitle]"
                            value="${data && data.subtitle ? data.subtitle : ''}"
                            placeholder="Subtitle (e.g. ${meta.subtitle})"
                            class="js-app-subtitle border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                        >
                        <input
                            type="url"
                            name="app_links[${index}][url]"
                            value="${data && data.url ? data.url : ''}"
                            placeholder="${meta.placeholder}"
                            required
                            class="js-app-url border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                        >
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-3 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-gray-200">
                        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="app_links[${index}][is_active]" value="1" class="js-app-active rounded accent-amber-500 w-3.5 h-3.5" ${!data || data.is_active ? 'checked' : ''}>
                            <span class="font-medium text-[11px]">Active</span>
                        </label>
                        <button type="button" class="js-app-remove p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 cursor-pointer" title="Remove Link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                `;

                const select = row.querySelector('.js-app-platform');
                const badge = row.querySelector('.js-app-icon-badge');
                const titleInp = row.querySelector('.js-app-title');
                const subInp = row.querySelector('.js-app-subtitle');
                const urlInp = row.querySelector('.js-app-url');
                const removeBtn = row.querySelector('.js-app-remove');
                const activeChk = row.querySelector('.js-app-active');

                select.addEventListener('change', function () {
                    const newMeta = appMeta(this.value);
                    badge.innerHTML = newMeta.icon_svg;
                    urlInp.placeholder = newMeta.placeholder;
                    titleInp.placeholder = `Title (e.g. ${newMeta.default_title})`;
                    subInp.placeholder = `Subtitle (e.g. ${newMeta.subtitle})`;
                    updateAppLivePreview();
                });

                [titleInp, subInp, urlInp, activeChk].forEach(function (el) {
                    el.addEventListener('input', updateAppLivePreview);
                    el.addEventListener('change', updateAppLivePreview);
                });

                removeBtn.addEventListener('click', function () {
                    row.remove();
                    appEmptyNotice.classList.toggle('hidden', appList.children.length > 0);
                    updateAppLivePreview();
                });

                appList.appendChild(row);
                appEmptyNotice.classList.add('hidden');
                updateAppLivePreview();
            }

            if (appAddBtn) {
                appAddBtn.addEventListener('click', function () {
                    addAppRow(null);
                });
            }

            if (loadDefaultAppsBtn) {
                loadDefaultAppsBtn.addEventListener('click', function () {
                    appList.innerHTML = '';
                    addAppRow({ platform: 'google-play', title: 'Google Play', subtitle: 'Download on', url: 'https://play.google.com/store/apps', is_active: true });
                    addAppRow({ platform: 'app-store', title: 'App Store', subtitle: 'Download on', url: 'https://apps.apple.com', is_active: true });
                });
            }

            // Populate initial App Links or offer defaults
            if (initialAppLinks && initialAppLinks.length > 0) {
                initialAppLinks.forEach(function (link) {
                    addAppRow(link);
                });
            } else {
                addAppRow({ platform: 'google-play', title: 'Google Play', subtitle: 'Download on', url: '', is_active: true });
                addAppRow({ platform: 'app-store', title: 'App Store', subtitle: 'Download on', url: '', is_active: true });
            }

            // Social Links Logic
            const socialList = document.getElementById('social_links_list');
            const socialAddBtn = document.getElementById('social_add_link_btn');
            const socialEmptyNotice = document.getElementById('social_links_empty');
            const previewSocialIcons = document.getElementById('preview_social_icons');
            const loadDefaultSocialsBtn = document.getElementById('load_default_socials_btn');

            let socialRowIndex = 0;

            function socialMeta(slug) {
                return socialPlatforms.find(function (p) { return p.slug === slug; }) || socialPlatforms[0];
            }

            function updateSocialLivePreview() {
                if (!previewSocialIcons) return;
                previewSocialIcons.innerHTML = '';

                const rows = socialList.querySelectorAll('.js-social-row');
                rows.forEach(function (row) {
                    const activeCheckbox = row.querySelector('.js-social-active');
                    if (activeCheckbox && !activeCheckbox.checked) return;

                    const select = row.querySelector('.js-social-platform');
                    const urlInput = row.querySelector('.js-social-url');

                    const meta = socialMeta(select.value);
                    const url = (urlInput && urlInput.value.trim()) || '#';

                    const a = document.createElement('a');
                    a.href = url;
                    a.target = '_blank';
                    a.className = 'w-9 h-9 rounded-full bg-slate-800/90 hover:bg-slate-700 text-white flex items-center justify-center transition-all shadow-xs';
                    a.title = meta.name;
                    a.innerHTML = meta.icon_svg;
                    previewSocialIcons.appendChild(a);
                });
            }

            function addSocialRow(data) {
                const index = socialRowIndex++;
                const currentPlatformSlug = data ? data.platform : (socialPlatforms[0]?.slug || 'whatsapp');
                const meta = socialMeta(currentPlatformSlug);

                const row = document.createElement('div');
                row.className = 'js-social-row flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl';

                let optionsHtml = '';
                socialPlatforms.forEach(function (p) {
                    const sel = p.slug === currentPlatformSlug ? 'selected' : '';
                    optionsHtml += `<option value="${p.slug}" ${sel}>${p.name}</option>`;
                });

                row.innerHTML = `
                    <span class="js-social-icon-badge w-8 h-8 rounded-lg border border-gray-200 bg-white text-gray-700 flex items-center justify-center shrink-0">
                        ${meta.icon_svg}
                    </span>
                    <select name="social_links[${index}][platform]" class="js-social-platform border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400 w-32 shrink-0">
                        ${optionsHtml}
                    </select>
                    <input
                        type="url"
                        name="social_links[${index}][url]"
                        value="${data && data.url ? data.url : ''}"
                        placeholder="${meta.placeholder}"
                        required
                        class="js-social-url flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    >
                    <label class="flex items-center gap-1 text-xs text-gray-600 cursor-pointer select-none shrink-0">
                        <input type="checkbox" name="social_links[${index}][is_active]" value="1" class="js-social-active rounded accent-indigo-500 w-3.5 h-3.5" ${!data || data.is_active ? 'checked' : ''}>
                        <span class="font-medium text-[11px]">Active</span>
                    </label>
                    <button type="button" class="js-social-remove p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 cursor-pointer shrink-0" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;

                const select = row.querySelector('.js-social-platform');
                const badge = row.querySelector('.js-social-icon-badge');
                const urlInp = row.querySelector('.js-social-url');
                const removeBtn = row.querySelector('.js-social-remove');
                const activeChk = row.querySelector('.js-social-active');

                select.addEventListener('change', function () {
                    const newMeta = socialMeta(this.value);
                    badge.innerHTML = newMeta.icon_svg;
                    urlInp.placeholder = newMeta.placeholder;
                    updateSocialLivePreview();
                });

                [urlInp, activeChk].forEach(function (el) {
                    el.addEventListener('input', updateSocialLivePreview);
                    el.addEventListener('change', updateSocialLivePreview);
                });

                removeBtn.addEventListener('click', function () {
                    row.remove();
                    socialEmptyNotice.classList.toggle('hidden', socialList.children.length > 0);
                    updateSocialLivePreview();
                });

                socialList.appendChild(row);
                socialEmptyNotice.classList.add('hidden');
                updateSocialLivePreview();
            }

            if (socialAddBtn) {
                socialAddBtn.addEventListener('click', function () {
                    addSocialRow(null);
                });
            }

            if (loadDefaultSocialsBtn) {
                loadDefaultSocialsBtn.addEventListener('click', function () {
                    socialList.innerHTML = '';
                    addSocialRow({ platform: 'whatsapp', url: 'https://wa.me/971508079199', is_active: true });
                    addSocialRow({ platform: 'facebook', url: 'https://facebook.com/openbox', is_active: true });
                    addSocialRow({ platform: 'youtube', url: 'https://youtube.com/@openbox', is_active: true });
                    addSocialRow({ platform: 'instagram', url: 'https://instagram.com/openbox', is_active: true });
                });
            }

            if (initialSocialLinks && initialSocialLinks.length > 0) {
                initialSocialLinks.forEach(function (link) {
                    addSocialRow(link);
                });
            } else {
                socialEmptyNotice.classList.remove('hidden');
            }
        });
    </script>
@endsection
