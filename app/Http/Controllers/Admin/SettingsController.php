<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateBrandingRequest;
use App\Http\Requests\Admin\Settings\UpdateMailRequest;
use App\Http\Requests\Admin\Settings\UpdatePaymentsRequest;
use App\Http\Requests\Admin\Settings\UpdateStorageRequest;
use App\Models\ActivityLog;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Services\ImageBackgroundRemover;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

use App\Services\Admin\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly MaintenanceService $maintenance
    ) {}

    public function branding(): View
    {
        return view('admin.settings.branding', [
            'fonts' => UpdateBrandingRequest::FONTS,
            'logo' => $this->settings->get('brand_logo'),
            'favicon' => $this->settings->get('brand_favicon'),
            'font' => $this->settings->get('brand_font', 'Montserrat'),
            'color' => $this->settings->get('brand_color_primary', '#f59e0b'),
        ]);
    }

    public function logo(): View
    {
        return view('admin.settings.logo', [
            'logo' => $this->settings->get('brand_logo'),
        ]);
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        // Delete old custom logo file if exists
        $oldLogo = $this->settings->get('brand_logo');
        if (!empty($oldLogo) && is_string($oldLogo) && trim($oldLogo) !== '' && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $path = $this->storeUploadedFile($request->file('logo'), 'branding', 'public');
        $this->settings->set('brand_logo', $path, 'branding');

        ActivityLog::record('settings.logo.updated');

        return back()->with('status', 'Site logo updated successfully.');
    }

    public function removeLogo(): RedirectResponse
    {
        $oldLogo = $this->settings->get('brand_logo');
        if (!empty($oldLogo) && is_string($oldLogo) && trim($oldLogo) !== '' && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $this->settings->set('brand_logo', null, 'branding');
        ActivityLog::record('settings.logo.removed');

        return back()->with('status', 'Custom logo removed. Default Openbox logo restored.');
    }

    public function banner(): View
    {
        return view('admin.settings.banner', [
            'banner' => $this->settings->get('brand_banner') ?: $this->settings->get('brand_cover_image'),
        ]);
    }

    public function hero(): View
    {
        return view('admin.settings.hero', [
            'heroImage' => $this->settings->get('home_hero_image'),
            'heroSlides' => HeroSlide::query()->with('product:id,title')->orderBy('sort_order')->orderBy('id')->get(),
            'products' => Product::query()->live()->orderBy('title')->limit(500)->get(['id', 'title']),
            'hero' => [
                'badge' => $this->settings->get('home_hero_badge', 'First 3 Months Free for New Vendors'),
                'title' => $this->settings->get('home_hero_title', 'Premium Electronics'),
                'highlight' => $this->settings->get('home_hero_highlight', 'Marketplace'),
                'subtitle' => $this->settings->get('home_hero_subtitle', 'Certified & graded electronics from verified sellers across Bangladesh. Every pre-owned item inspected, graded, and covered by our Openbox Guarantee.'),
            ],
        ]);
    }

    public function updateHeroText(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'badge' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:120'],
            'highlight' => ['nullable', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:500'],
        ]);

        $this->settings->setMany([
            'home_hero_badge' => $data['badge'] ?? '',
            'home_hero_title' => $data['title'],
            'home_hero_highlight' => $data['highlight'] ?? '',
            'home_hero_subtitle' => $data['subtitle'] ?? '',
        ], 'branding');

        ActivityLog::record('settings.hero_text.updated');

        return back()->with('status', 'Homepage hero text updated successfully.');
    }

    public function updateHeroImage(Request $request): RedirectResponse
    {
        $request->validate([
            'hero_image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ]);

        $oldImage = $this->settings->get('home_hero_image');
        if (!empty($oldImage) && is_string($oldImage) && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        $path = $this->storeUploadedFile($request->file('hero_image'), 'branding', 'public');
        $this->settings->set('home_hero_image', $path, 'branding');

        ActivityLog::record('settings.hero_image.updated');

        return back()->with('status', 'Homepage hero image updated successfully.');
    }

    public function storeHeroSlides(Request $request): RedirectResponse
    {
        $request->validate([
            'slides' => ['required', 'array', 'max:10'],
            'slides.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ]);

        $nextOrder = (int) HeroSlide::query()->max('sort_order') + 1;

        foreach ($request->file('slides') as $file) {
            HeroSlide::create([
                'image' => $this->storeUploadedFile($file, 'branding', 'public'),
                'sort_order' => $nextOrder++,
            ]);
        }

        ActivityLog::record('settings.hero_slides.added');

        return back()->with('status', 'Hero slides uploaded. Assign a product to each slide below.');
    }

    public function updateHeroSlide(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'enable_overlay' => ['nullable', 'boolean'],
            'badge_text' => ['nullable', 'string', 'max:150'],
            'badge_bg_color' => ['nullable', 'string', 'max:30'],
            'badge_text_color' => ['nullable', 'string', 'max:30'],
            'badge_font_size' => ['nullable', 'integer', 'min:8', 'max:120'],
            'title_line1' => ['nullable', 'string', 'max:150'],
            'title_line1_color' => ['nullable', 'string', 'max:30'],
            'title_line1_font_size' => ['nullable', 'integer', 'min:8', 'max:160'],
            'title_line2' => ['nullable', 'string', 'max:150'],
            'title_line2_color' => ['nullable', 'string', 'max:30'],
            'title_line2_font_size' => ['nullable', 'integer', 'min:8', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'subtitle_color' => ['nullable', 'string', 'max:30'],
            'subtitle_font_size' => ['nullable', 'integer', 'min:8', 'max:120'],
            'callout_text' => ['nullable', 'string', 'max:255'],
            'callout_color' => ['nullable', 'string', 'max:30'],
            'callout_accent_color' => ['nullable', 'string', 'max:30'],
            'callout_font_size' => ['nullable', 'integer', 'min:8', 'max:120'],
            'features' => ['nullable', 'array'],
            'features.*.icon' => ['nullable', 'string', 'max:50'],
            'features.*.title' => ['nullable', 'string', 'max:100'],
            'features.*.subtitle' => ['nullable', 'string', 'max:100'],
            'features.*.color' => ['nullable', 'string', 'max:30'],
            'features.*.icon_size' => ['nullable', 'integer', 'min:8', 'max:120'],
            'features.*.title_size' => ['nullable', 'integer', 'min:8', 'max:80'],
            'features.*.subtitle_size' => ['nullable', 'integer', 'min:8', 'max:80'],
        ]);

        if ($request->hasFile('image')) {
            if (is_string($heroSlide->image) && Storage::disk('public')->exists($heroSlide->image)) {
                Storage::disk('public')->delete($heroSlide->image);
            }

            $heroSlide->image = $this->storeUploadedFile($request->file('image'), 'branding', 'public');
        }

        $features = [];
        if ($request->has('features') && is_array($request->input('features'))) {
            foreach ($request->input('features') as $f) {
                if (!empty($f['title']) || !empty($f['subtitle'])) {
                    $features[] = [
                        'icon' => $f['icon'] ?? 'shield-check',
                        'title' => $f['title'] ?? '',
                        'subtitle' => $f['subtitle'] ?? '',
                        'color' => $f['color'] ?? '#EAB308',
                        'icon_size' => !empty($f['icon_size']) ? (int) $f['icon_size'] : null,
                        'title_size' => !empty($f['title_size']) ? (int) $f['title_size'] : null,
                        'subtitle_size' => !empty($f['subtitle_size']) ? (int) $f['subtitle_size'] : null,
                    ];
                }
            }
        }

        $heroSlide->update([
            'product_id' => $data['product_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? $heroSlide->sort_order,
            'is_active' => $request->boolean('is_active'),
            'enable_overlay' => $request->boolean('enable_overlay'),
            'badge_text' => $data['badge_text'] ?? null,
            'badge_bg_color' => $data['badge_bg_color'] ?? '#FEF08A',
            'badge_text_color' => $data['badge_text_color'] ?? '#854D0E',
            'badge_font_size' => !empty($data['badge_font_size']) ? (int) $data['badge_font_size'] : null,
            'title_line1' => $data['title_line1'] ?? null,
            'title_line1_color' => $data['title_line1_color'] ?? '#0F172A',
            'title_line1_font_size' => !empty($data['title_line1_font_size']) ? (int) $data['title_line1_font_size'] : null,
            'title_line2' => $data['title_line2'] ?? null,
            'title_line2_color' => $data['title_line2_color'] ?? '#EAB308',
            'title_line2_font_size' => !empty($data['title_line2_font_size']) ? (int) $data['title_line2_font_size'] : null,
            'subtitle' => $data['subtitle'] ?? null,
            'subtitle_color' => $data['subtitle_color'] ?? '#475569',
            'subtitle_font_size' => !empty($data['subtitle_font_size']) ? (int) $data['subtitle_font_size'] : null,
            'callout_text' => $data['callout_text'] ?? null,
            'callout_color' => $data['callout_color'] ?? '#1E3A8A',
            'callout_accent_color' => $data['callout_accent_color'] ?? '#EAB308',
            'callout_font_size' => !empty($data['callout_font_size']) ? (int) $data['callout_font_size'] : null,
            'features' => $request->has('features') ? $features : $heroSlide->features,
        ]);

        ActivityLog::record('settings.hero_slides.updated', properties: ['slide' => $heroSlide->id]);

        return back()->with('status', 'Hero slide updated.');
    }

    public function destroyHeroSlide(HeroSlide $heroSlide): RedirectResponse
    {
        if (is_string($heroSlide->image) && Storage::disk('public')->exists($heroSlide->image)) {
            Storage::disk('public')->delete($heroSlide->image);
        }

        $heroSlide->delete();
        ActivityLog::record('settings.hero_slides.removed');

        return back()->with('status', 'Hero slide removed.');
    }

    public function removeHeroImage(): RedirectResponse
    {
        $oldImage = $this->settings->get('home_hero_image');
        if (!empty($oldImage) && is_string($oldImage) && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        $this->settings->set('home_hero_image', null, 'branding');
        ActivityLog::record('settings.hero_image.removed');

        return back()->with('status', 'Homepage hero image removed.');
    }

    public function updateBanner(Request $request): RedirectResponse
    {
        $request->validate([
            'banner' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:5120'],
        ]);

        // Delete old custom banner file if exists
        $oldBanner = $this->settings->get('brand_banner') ?: $this->settings->get('brand_cover_image');
        if (!empty($oldBanner) && is_string($oldBanner) && trim($oldBanner) !== '' && Storage::disk('public')->exists($oldBanner)) {
            Storage::disk('public')->delete($oldBanner);
        }

        $path = $this->storeUploadedFile($request->file('banner'), 'branding', 'public');
        $this->settings->set('brand_banner', $path, 'branding');
        $this->settings->set('brand_cover_image', $path, 'branding');

        ActivityLog::record('settings.banner.updated');

        return back()->with('status', 'Store banner / cover image updated successfully.');
    }

    public function removeBanner(): RedirectResponse
    {
        $oldBanner = $this->settings->get('brand_banner') ?: $this->settings->get('brand_cover_image');
        if (!empty($oldBanner) && is_string($oldBanner) && trim($oldBanner) !== '' && Storage::disk('public')->exists($oldBanner)) {
            Storage::disk('public')->delete($oldBanner);
        }

        $this->settings->set('brand_banner', null, 'branding');
        $this->settings->set('brand_cover_image', null, 'branding');
        ActivityLog::record('settings.banner.removed');

        return back()->with('status', 'Custom banner removed. Default banner restored.');
    }

    public function sellerBanner(): View
    {
        return view('admin.settings.seller-banner', [
            'banner' => $this->settings->get('seller_banner'),
        ]);
    }

    public function updateSellerBanner(Request $request): RedirectResponse
    {
        $request->validate([
            'banner' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:5120'],
        ]);

        $oldBanner = $this->settings->get('seller_banner');
        if (!empty($oldBanner) && is_string($oldBanner) && trim($oldBanner) !== '' && Storage::disk('public')->exists($oldBanner)) {
            Storage::disk('public')->delete($oldBanner);
        }

        $path = $this->storeUploadedFile($request->file('banner'), 'branding', 'public');
        $this->settings->set('seller_banner', $path, 'branding');

        ActivityLog::record('settings.seller_banner.updated');

        return back()->with('status', 'Default seller banner / cover photo updated successfully.');
    }

    public function removeSellerBanner(): RedirectResponse
    {
        $oldBanner = $this->settings->get('seller_banner');
        if (!empty($oldBanner) && is_string($oldBanner) && trim($oldBanner) !== '' && Storage::disk('public')->exists($oldBanner)) {
            Storage::disk('public')->delete($oldBanner);
        }

        $this->settings->set('seller_banner', null, 'branding');
        ActivityLog::record('settings.seller_banner.removed');

        return back()->with('status', 'Custom seller banner removed. Default fallback banner restored.');
    }

    public function siteicon(): View
    {
        return view('admin.settings.siteicon', [
            'favicon' => $this->settings->get('brand_favicon'),
        ]);
    }

    public function updateSiteicon(Request $request): RedirectResponse
    {
        $request->validate([
            'favicon' => ['required', 'file', 'mimes:ico,png,svg,jpg,jpeg,webp', 'max:1024'],
        ]);

        $oldFavicon = $this->settings->get('brand_favicon');
        if (!empty($oldFavicon) && is_string($oldFavicon) && trim($oldFavicon) !== '' && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $path = $this->storeUploadedFile($request->file('favicon'), 'branding', 'public');
        $this->settings->set('brand_favicon', $path, 'branding');

        ActivityLog::record('settings.siteicon.updated');

        return back()->with('status', 'Site icon (favicon) updated successfully.');
    }

    public function removeSiteicon(): RedirectResponse
    {
        $oldFavicon = $this->settings->get('brand_favicon');
        if (!empty($oldFavicon) && is_string($oldFavicon) && trim($oldFavicon) !== '' && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $this->settings->set('brand_favicon', null, 'branding');
        ActivityLog::record('settings.siteicon.removed');

        return back()->with('status', 'Custom site icon removed. Default favicon restored.');
    }

    public function verifiedBadge(): View
    {
        return view('admin.settings.verified-badge', [
            'badge' => \App\Support\MediaUrl::resolve($this->settings->get('brand_verified_badge')),
            'badgeSize' => (int) ($this->settings->get('brand_verified_badge_size') ?: 24),
        ]);
    }

    public function updateVerifiedBadge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'badge' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:1024'],
            'size' => ['required', 'integer', 'between:12,64'],
        ]);

        $this->settings->set('brand_verified_badge_size', (string) $data['size'], 'branding');

        if ($file = $request->file('badge')) {
            $remover = app(ImageBackgroundRemover::class);
            $isSvg = strtolower($file->getClientOriginalExtension()) === 'svg';
            $cutout = $isSvg
                ? null
                : ($remover->removeNeutralBackdrop($file->getRealPath()) ?? $remover->process($file->getRealPath()));

            $this->deleteStoredFile($this->settings->get('brand_verified_badge'));

            if ($cutout) {
                $path = 'branding/'.Str::random(40).'.png';
                Storage::disk('public')->put($path, file_get_contents($cutout));
                @unlink($cutout);
            } else {
                $path = $this->storeUploadedFile($file, 'branding', 'public');
            }

            $this->settings->set('brand_verified_badge', $path, 'branding');
        }

        ActivityLog::record('settings.verified_badge.updated');

        return back()->with('status', 'Verified badge updated successfully.');
    }

    public function removeVerifiedBadge(): RedirectResponse
    {
        $this->deleteStoredFile($this->settings->get('brand_verified_badge'));

        $this->settings->set('brand_verified_badge', null, 'branding');
        ActivityLog::record('settings.verified_badge.removed');

        return back()->with('status', 'Custom verified badge removed. Default check icon restored.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function footericon(): View
    {
        $footerBgColor = $this->settings->get('brand_footer_bg_color', '#0c0f1d') ?: '#0c0f1d';
        $footerBottomBgColor = $this->settings->get('brand_footer_bottom_bg_color') ?: $footerBgColor;

        return view('admin.settings.footericon', [
            'footerIcon' => $this->settings->get('brand_footer_icon'),
            'footerDescription' => $this->settings->get('brand_footer_description', "Bangladesh's premier verified electronics marketplace. Every device inspected, graded, and warehoused for authentic peace of mind."),
            'footerAddress' => $this->settings->get('brand_footer_address', 'Dhaka, Bangladesh'),
            'footerCopyright' => $this->settings->get('brand_footer_copyright', '© ' . now()->year . ' Openbox. All rights reserved.'),
            'footerBgColor' => $footerBgColor,
            'footerBottomBgColor' => $footerBottomBgColor,
        ]);
    }

    public function updateFootericon(Request $request): RedirectResponse
    {
        $request->validate([
            'footer_icon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:2048'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'footer_address' => ['nullable', 'string', 'max:255'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
            'footer_bg_color' => ['nullable', 'string', 'max:30'],
            'footer_bottom_bg_color' => ['nullable', 'string', 'max:30'],
        ]);

        if ($request->hasFile('footer_icon')) {
            $oldIcon = $this->settings->get('brand_footer_icon');
            if (!empty($oldIcon) && is_string($oldIcon) && trim($oldIcon) !== '' && Storage::disk('public')->exists($oldIcon)) {
                Storage::disk('public')->delete($oldIcon);
            }

            $path = $this->storeUploadedFile($request->file('footer_icon'), 'branding', 'public');
            $this->settings->set('brand_footer_icon', $path, 'branding');
        }

        if ($request->has('footer_description')) {
            $this->settings->set('brand_footer_description', $request->input('footer_description'), 'branding');
        }

        if ($request->has('footer_address')) {
            $this->settings->set('brand_footer_address', $request->input('footer_address'), 'branding');
        }

        if ($request->has('footer_copyright')) {
            $this->settings->set('brand_footer_copyright', $request->input('footer_copyright'), 'branding');
        }

        if ($request->has('footer_bg_color')) {
            $footerBg = $request->input('footer_bg_color') ?: '#0c0f1d';
            $this->settings->set('brand_footer_bg_color', $footerBg, 'branding');

            $footerBottomBg = $request->filled('footer_bottom_bg_color')
                ? $request->input('footer_bottom_bg_color')
                : $footerBg;
            $this->settings->set('brand_footer_bottom_bg_color', $footerBottomBg, 'branding');
        }

        ActivityLog::record('settings.footericon.updated');

        return back()->with('status', 'Footer settings updated successfully.');
    }

    public function removeFootericon(): RedirectResponse
    {
        $oldIcon = $this->settings->get('brand_footer_icon');
        if (!empty($oldIcon) && is_string($oldIcon) && trim($oldIcon) !== '' && Storage::disk('public')->exists($oldIcon)) {
            Storage::disk('public')->delete($oldIcon);
        }

        $this->settings->set('brand_footer_icon', null, 'branding');
        ActivityLog::record('settings.footericon.removed');

        return back()->with('status', 'Custom footer icon removed. Default icon restored.');
    }

    public function appLinks(): View
    {
        $rawAppLinks = $this->settings->get('footer_app_links');
        $appLinks = !empty($rawAppLinks) ? (is_string($rawAppLinks) ? json_decode($rawAppLinks, true) : (array) $rawAppLinks) : [];
        if (!is_array($appLinks)) {
            $appLinks = [];
        }

        $rawSocialLinks = $this->settings->get('footer_social_links');
        $socialLinks = !empty($rawSocialLinks) ? (is_string($rawSocialLinks) ? json_decode($rawSocialLinks, true) : (array) $rawSocialLinks) : [];
        if (!is_array($socialLinks)) {
            $socialLinks = [];
        }

        $appHeading = $this->settings->get('footer_app_heading', 'Experience '.config('app.name', 'Openbox').' App on your mobile:');

        return view('admin.settings.app-links', [
            'appHeading' => $appHeading,
            'appLinks' => $appLinks,
            'socialLinks' => $socialLinks,
            'appPlatforms' => array_values(\App\Support\FooterAppLinks::APP_PLATFORMS),
            'socialPlatforms' => array_values(\App\Support\FooterAppLinks::SOCIAL_PLATFORMS),
        ]);
    }

    public function updateAppLinks(Request $request): RedirectResponse
    {
        $request->validate([
            'footer_app_heading' => ['nullable', 'string', 'max:250'],
            'app_links' => ['nullable', 'array', 'max:10'],
            'app_links.*.platform' => ['required', 'string', 'max:50'],
            'app_links.*.url' => ['nullable', 'string', 'max:500'],
            'app_links.*.title' => ['nullable', 'string', 'max:100'],
            'app_links.*.subtitle' => ['nullable', 'string', 'max:100'],

            'social_links' => ['nullable', 'array', 'max:15'],
            'social_links.*.platform' => ['required', 'string', 'max:50'],
            'social_links.*.url' => ['nullable', 'string', 'max:500'],
        ]);

        $appLinks = [];
        foreach ($request->input('app_links', []) as $item) {
            $platform = trim((string) ($item['platform'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            if ($platform !== '' && $url !== '') {
                $appLinks[] = [
                    'platform' => $platform,
                    'url' => $url,
                    'title' => trim((string) ($item['title'] ?? '')) ?: null,
                    'subtitle' => trim((string) ($item['subtitle'] ?? '')) ?: null,
                    'is_active' => !empty($item['is_active']),
                ];
            }
        }

        $socialLinks = [];
        foreach ($request->input('social_links', []) as $item) {
            $platform = trim((string) ($item['platform'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            if ($platform !== '' && $url !== '') {
                $socialLinks[] = [
                    'platform' => $platform,
                    'url' => $url,
                    'is_active' => !empty($item['is_active']),
                ];
            }
        }

        $this->settings->set('footer_app_heading', $request->string('footer_app_heading')->value() ?: null, 'branding');
        $this->settings->set('footer_app_links', json_encode($appLinks), 'branding');
        $this->settings->set('footer_social_links', json_encode($socialLinks), 'branding');

        ActivityLog::record('settings.app_links.updated');

        return back()->with('status', 'App & social links updated successfully.');
    }

    public function font(): View

    {
        return view('admin.settings.font', [
            'fonts' => UpdateBrandingRequest::FONTS,
            'currentFont' => $this->settings->get('brand_font', 'Montserrat'),
        ]);
    }

    public function updateFont(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_font' => ['required', Rule::in(UpdateBrandingRequest::FONTS)],
        ]);

        $this->settings->set('brand_font', $request->string('brand_font')->value(), 'branding');
        ActivityLog::record('settings.font.updated', properties: ['font' => $request->string('brand_font')->value()]);

        return back()->with('status', 'Site typography updated to '.$request->string('brand_font')->value().'.');
    }

    public function color(): View
    {
        $primary = $this->settings->get('brand_color_primary', '#f59e0b');
        $base = $this->settings->get('brand_color_base', $primary);
        $weight = (string) $this->settings->get('brand_color_weight', '500');

        return view('admin.settings.color', [
            'currentColor' => $primary,
            'currentBaseColor' => $base,
            'currentWeight' => $weight,
            'currentHoverColor' => $this->settings->get('brand_color_hover', '#fff200'),
            'currentHoverTextColor' => $this->settings->get('brand_color_hover_text', '#000000'),
            'currentTextColor' => $this->settings->get('site_text_color', '#1f2937'),
            'currentHeadingColor' => $this->settings->get('site_heading_color', '#0f172a'),
            'currentMutedColor' => $this->settings->get('site_muted_color', '#64748b'),
        ]);
    }

    public function updateColor(Request $request): RedirectResponse
    {
        $primary = '#'.ltrim(trim($request->string('brand_color_primary')->value()), '#');
        $base = $request->filled('brand_color_base')
            ? '#'.ltrim(trim($request->string('brand_color_base')->value()), '#')
            : $primary;
        $weight = $request->filled('brand_color_weight')
            ? trim($request->string('brand_color_weight')->value())
            : '500';
        $hover = $request->filled('brand_color_hover')
            ? '#'.ltrim(trim($request->string('brand_color_hover')->value()), '#')
            : '#fff200';
        $hoverText = $request->filled('brand_color_hover_text')
            ? '#'.ltrim(trim($request->string('brand_color_hover_text')->value()), '#')
            : '#000000';
        $siteTextColor = $request->filled('site_text_color')
            ? '#'.ltrim(trim($request->string('site_text_color')->value()), '#')
            : '#1f2937';
        $siteHeadingColor = $request->filled('site_heading_color')
            ? '#'.ltrim(trim($request->string('site_heading_color')->value()), '#')
            : '#0f172a';
        $siteMutedColor = $request->filled('site_muted_color')
            ? '#'.ltrim(trim($request->string('site_muted_color')->value()), '#')
            : '#64748b';

        $request->merge([
            'brand_color_primary' => $primary,
            'brand_color_base' => $base,
            'brand_color_weight' => $weight,
            'brand_color_hover' => $hover,
            'brand_color_hover_text' => $hoverText,
            'site_text_color' => $siteTextColor,
            'site_heading_color' => $siteHeadingColor,
            'site_muted_color' => $siteMutedColor,
        ]);

        $request->validate([
            'brand_color_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_color_base' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_color_weight' => ['required', 'in:50,100,200,300,400,500,600,700,800,900'],
            'brand_color_hover' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_color_hover_text' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'site_text_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'site_heading_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'site_muted_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $hex = strtolower($request->string('brand_color_primary')->value());
        $baseHex = strtolower($request->string('brand_color_base')->value());
        $weightVal = $request->string('brand_color_weight')->value();
        $hoverHex = strtolower($request->string('brand_color_hover')->value());
        $hoverTextHex = strtolower($request->string('brand_color_hover_text')->value());

        $this->settings->setMany([
            'brand_color_primary' => $hex,
            'brand_color_base' => $baseHex,
            'brand_color_weight' => $weightVal,
            'brand_color_hover' => $hoverHex,
            'brand_color_hover_text' => $hoverTextHex,
            'site_text_color' => strtolower($siteTextColor),
            'site_heading_color' => strtolower($siteHeadingColor),
            'site_muted_color' => strtolower($siteMutedColor),
        ], 'branding');

        ActivityLog::record('settings.color.updated', properties: [
            'color' => $hex,
            'base' => $baseHex,
            'weight' => $weightVal,
            'hover_color' => $hoverHex,
            'hover_text' => $hoverTextHex,
            'site_text_color' => strtolower($siteTextColor),
            'site_heading_color' => strtolower($siteHeadingColor),
            'site_muted_color' => strtolower($siteMutedColor),
        ]);

        return back()->with('status', 'Site brand colors and hover styles updated successfully.');
    }

    public function productCard(): View
    {
        return view('admin.settings.product-card', [
            'categoryColor' => $this->settings->get('product_card_category_color', '#ea580c'),
            'categorySize' => (int) $this->settings->get('product_card_category_size', 11),

            'titleColor' => $this->settings->get('product_card_title_color', '#090d16'),
            'titleHoverColor' => $this->settings->get('product_card_title_hover_color', '#ea580c'),
            'titleSize' => (int) $this->settings->get('product_card_title_size', 14),

            'priceColor' => $this->settings->get('product_card_price_color', '#090d16'),
            'priceSize' => (int) $this->settings->get('product_card_price_size', 16),
            'oldPriceColor' => $this->settings->get('product_card_old_price_color', '#94a3b8'),
            'oldPriceSize' => (int) $this->settings->get('product_card_old_price_size', 12),

            'starColor' => $this->settings->get('product_card_star_color', '#fbbf24'),
            'ratingColor' => $this->settings->get('product_card_rating_color', '#1e293b'),
            'ratingCountColor' => $this->settings->get('product_card_rating_count_color', '#94a3b8'),
            'ratingSize' => (int) $this->settings->get('product_card_rating_size', 12),

            'vendorColor' => $this->settings->get('product_card_vendor_color', '#64748b'),
            'vendorSize' => (int) $this->settings->get('product_card_vendor_size', 12),

            'btnBg' => $this->settings->get('product_card_btn_bg', $this->settings->get('brand_color_primary', '#656c19')),
            'btnText' => $this->settings->get('product_card_btn_text', '#ffffff'),
            'btnSize' => (int) $this->settings->get('product_card_btn_size', 12),
            'btnHoverBg' => $this->settings->get('product_card_btn_hover_bg', $this->settings->get('brand_color_hover', '#fff200')),
            'btnHoverText' => $this->settings->get('product_card_btn_hover_text', '#000000'),
        ]);
    }

    public function updateProductCard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_card_category_color' => ['nullable', 'string', 'max:30'],
            'product_card_category_size' => ['nullable', 'integer', 'min:8', 'max:32'],

            'product_card_title_color' => ['nullable', 'string', 'max:30'],
            'product_card_title_hover_color' => ['nullable', 'string', 'max:30'],
            'product_card_title_size' => ['nullable', 'integer', 'min:8', 'max:36'],

            'product_card_price_color' => ['nullable', 'string', 'max:30'],
            'product_card_price_size' => ['nullable', 'integer', 'min:8', 'max:48'],
            'product_card_old_price_color' => ['nullable', 'string', 'max:30'],
            'product_card_old_price_size' => ['nullable', 'integer', 'min:8', 'max:36'],

            'product_card_star_color' => ['nullable', 'string', 'max:30'],
            'product_card_rating_color' => ['nullable', 'string', 'max:30'],
            'product_card_rating_count_color' => ['nullable', 'string', 'max:30'],
            'product_card_rating_size' => ['nullable', 'integer', 'min:8', 'max:32'],

            'product_card_vendor_color' => ['nullable', 'string', 'max:30'],
            'product_card_vendor_size' => ['nullable', 'integer', 'min:8', 'max:32'],

            'product_card_btn_bg' => ['nullable', 'string', 'max:30'],
            'product_card_btn_text' => ['nullable', 'string', 'max:30'],
            'product_card_btn_size' => ['nullable', 'integer', 'min:8', 'max:32'],
            'product_card_btn_hover_bg' => ['nullable', 'string', 'max:30'],
            'product_card_btn_hover_text' => ['nullable', 'string', 'max:30'],
        ]);

        foreach ($validated as $key => $val) {
            if ($val !== null && trim((string) $val) !== '') {
                $this->settings->set($key, is_string($val) ? trim($val) : (string) $val, 'branding');
            }
        }

        ActivityLog::record('settings.product_card.updated');

        return back()->with('status', 'Product card styling settings updated successfully.');
    }

    public function resetProductCard(): RedirectResponse
    {
        $keys = [
            'product_card_category_color',
            'product_card_category_size',
            'product_card_title_color',
            'product_card_title_hover_color',
            'product_card_title_size',
            'product_card_price_color',
            'product_card_price_size',
            'product_card_old_price_color',
            'product_card_old_price_size',
            'product_card_star_color',
            'product_card_rating_color',
            'product_card_rating_count_color',
            'product_card_rating_size',
            'product_card_vendor_color',
            'product_card_vendor_size',
            'product_card_btn_bg',
            'product_card_btn_text',
            'product_card_btn_size',
            'product_card_btn_hover_bg',
            'product_card_btn_hover_text',
        ];

        foreach ($keys as $k) {
            $this->settings->set($k, null, 'branding');
        }

        ActivityLog::record('settings.product_card.reset');

        return back()->with('status', 'Product card styling reset to default values.');
    }

    public function cache(): View
    {
        return view('admin.settings.cache', [
            'actions' => $this->maintenance->availableActions(),
        ]);
    }

    public function clearCache(string $action = 'all-clear'): RedirectResponse
    {
        try {
            $result = $this->maintenance->run($action);
        } catch (\InvalidArgumentException) {
            abort(404);
        }

        ActivityLog::record('settings.cache.'.$action, properties: ['output' => $result['output']]);

        return back()->with('status', $result['label'].'.');
    }

    public function updateBranding(UpdateBrandingRequest $request): RedirectResponse
    {
        if ($request->hasFile('logo')) {
            $this->settings->set('brand_logo', $this->storeUploadedFile($request->file('logo'), 'branding', 'public'), 'branding');
        }

        if ($request->hasFile('favicon')) {
            $this->settings->set('brand_favicon', $this->storeUploadedFile($request->file('favicon'), 'branding', 'public'), 'branding');
        }

        $this->settings->set('brand_font', $request->string('brand_font')->value() ?: null, 'branding');
        $this->settings->set('brand_color_primary', $request->string('brand_color_primary')->value() ?: null, 'branding');

        ActivityLog::record('settings.branding.updated');

        return back()->with('status', 'Branding updated.');
    }

    /**
     * Store an uploaded file safely, handling PHP 8.4 / Windows temp file paths.
     */
    private function storeUploadedFile(\Illuminate\Http\UploadedFile $file, string $directory = 'branding', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $filename = \Illuminate\Support\Str::random(40) . '.' . strtolower($extension);
        $targetPath = trim($directory, '/') . '/' . $filename;

        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        if (!empty($sourcePath) && file_exists($sourcePath)) {
            $stream = @fopen($sourcePath, 'r');
            if ($stream !== false) {
                try {
                    Storage::disk($disk)->put($targetPath, $stream);
                    return $targetPath;
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
        }

        return $file->store($directory, $disk);
    }


    public function mail(): View
    {
        return view('admin.settings.mail', [
            'values' => [
                'mail_host' => $this->settings->get('mail_host'),
                'mail_port' => $this->settings->get('mail_port', '587'),
                'mail_encryption' => $this->settings->get('mail_encryption', 'tls'),
                'mail_username' => $this->settings->get('mail_username'),
                'mail_from_address' => $this->settings->get('mail_from_address', config('mail.from.address')),
                'mail_from_name' => $this->settings->get('mail_from_name', config('app.name')),
            ],
            'hasPassword' => $this->settings->has('mail_password'),
        ]);
    }

    public function updateMail(UpdateMailRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'mail_host' => $request->string('mail_host')->value(),
            'mail_port' => (string) $request->integer('mail_port'),
            'mail_encryption' => $request->string('mail_encryption')->value(),
            'mail_username' => $request->string('mail_username')->value() ?: null,
            'mail_from_address' => $request->string('mail_from_address')->value(),
            'mail_from_name' => $request->string('mail_from_name')->value(),
        ], 'mail');

        if ($request->filled('mail_password')) {
            $this->settings->set('mail_password', $request->string('mail_password')->value(), 'mail');
        }

        ActivityLog::record('settings.mail.updated');

        return back()->with('status', 'SMTP settings updated.');
    }

    public function sendTestMail(): RedirectResponse
    {
        try {
            Mail::raw('This is a test email from your Openbox admin settings.', function ($message) {
                $message->to(request()->user()->email)->subject('Openbox SMTP test');
            });
        } catch (Throwable $e) {
            return back()->withErrors(['mail_host' => 'Could not send test email: '.$e->getMessage()]);
        }

        ActivityLog::record('settings.mail.tested');

        return back()->with('status', 'Test email sent to '.request()->user()->email.'.');
    }

    public function storage(): View
    {
        $defaultCloudName = 'w36cggya';
        $defaultApiKey = '146238954648692';
        $defaultApiSecret = 'BEuXx-vqfq-tPmWMTaRbJd6SyH8';
        $defaultUrl = 'cloudinary://146238954648692:BEuXx-vqfq-tPmWMTaRbJd6SyH8@w36cggya';

        // Auto-seed default user Cloudinary credentials if not saved yet
        if (! $this->settings->has('cloudinary_cloud_name')) {
            $this->settings->setMany([
                'cloudinary_cloud_name' => $defaultCloudName,
                'cloudinary_api_key' => $defaultApiKey,
                'cloudinary_url' => $defaultUrl,
            ], 'storage');
            $this->settings->set('cloudinary_api_secret', $defaultApiSecret, 'storage');
        }

        $historyLogs = ActivityLog::query()
            ->with('user')
            ->where('action', 'like', 'settings.storage%')
            ->latest('id')
            ->paginate(5, ['*'], 'history_page')
            ->fragment('storage-history');

        return view('admin.settings.storage', [
            'values' => [
                'r2_access_key_id' => $this->settings->get('r2_access_key_id'),
                'r2_bucket' => $this->settings->get('r2_bucket'),
                'r2_endpoint' => $this->settings->get('r2_endpoint'),
                'r2_url' => $this->settings->get('r2_url'),
                'r2_region' => $this->settings->get('r2_region', 'auto'),
                'cloudinary_cloud_name' => $this->settings->get('cloudinary_cloud_name', $defaultCloudName),
                'cloudinary_api_key' => $this->settings->get('cloudinary_api_key', $defaultApiKey),
                'cloudinary_url' => $this->settings->get('cloudinary_url', $defaultUrl),
                'storage_disk' => $this->settings->get('storage_disk', $this->settings->has('cloudinary_cloud_name') ? 'cloudinary' : 'public'),
                'storage_cdn_url' => $this->settings->get('storage_cdn_url'),
            ],
            'hasR2Secret' => $this->settings->has('r2_secret_access_key'),
            'hasCloudinarySecret' => $this->settings->has('cloudinary_api_secret') || ! empty($defaultApiSecret),
            'historyLogs' => $historyLogs,
        ]);
    }

    public function clearStorageHistory(): RedirectResponse
    {
        ActivityLog::query()
            ->where('action', 'like', 'settings.storage%')
            ->delete();

        ActivityLog::record('settings.storage.history_cleared');

        return redirect()->route('admin.settings.storage')->with('status', 'Storage settings change history cleared successfully.');
    }

    public function updateActiveStorage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'storage_disk' => ['required', 'string', 'in:public,r2,cloudinary'],
            'storage_cdn_url' => ['nullable', 'url', 'max:500'],
        ]);

        $previousDisk = $this->settings->get('storage_disk', 'public');
        $newDisk = $validated['storage_disk'];
        $cdnUrl = trim((string) ($validated['storage_cdn_url'] ?? ''));

        $this->settings->set('storage_disk', $newDisk, 'storage');
        $this->settings->set('storage_cdn_url', $cdnUrl ?: null, 'storage');
        config(['filesystems.default' => $newDisk]);

        try {
            \Illuminate\Support\Facades\Artisan::call('storage:sync-urls', ['--disk' => $newDisk]);
        } catch (\Throwable) {
            // Ignore if background task fails
        }

        ActivityLog::record('settings.storage.active_disk_changed', null, [
            'from_disk' => $previousDisk,
            'to_disk' => $newDisk,
        ]);

        return back()->with('status', 'Active storage driver updated to '.strtoupper($newDisk).' and database URLs synced.');
    }

    public function updateCloudinaryStorage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cloudinary_cloud_name' => ['nullable', 'string', 'max:255'],
            'cloudinary_api_key' => ['nullable', 'string', 'max:255'],
            'cloudinary_api_secret' => ['nullable', 'string', 'max:255'],
            'cloudinary_url' => ['nullable', 'string', 'max:500'],
        ]);

        $cloudName = trim((string) ($validated['cloudinary_cloud_name'] ?? ''));
        $apiKey = trim((string) ($validated['cloudinary_api_key'] ?? ''));
        $apiSecret = trim((string) ($validated['cloudinary_api_secret'] ?? ''));
        $cloudinaryUrl = trim((string) ($validated['cloudinary_url'] ?? ''));

        // Auto-parse CLOUDINARY_URL string if provided (e.g. cloudinary://API_KEY:API_SECRET@CLOUD_NAME)
        if (str_starts_with($cloudinaryUrl, 'cloudinary://') && preg_match('#^cloudinary://([^:]+):([^@]+)@(.+)$#i', $cloudinaryUrl, $matches)) {
            if (empty($apiKey)) {
                $apiKey = $matches[1];
            }
            if (empty($apiSecret)) {
                $apiSecret = $matches[2];
            }
            if (empty($cloudName)) {
                $cloudName = $matches[3];
            }
        } elseif (str_starts_with($cloudName, 'cloudinary://') && preg_match('#^cloudinary://([^:]+):([^@]+)@(.+)$#i', $cloudName, $matches)) {
            $apiKey = $matches[1];
            $apiSecret = $matches[2];
            $cloudName = $matches[3];
            $cloudinaryUrl = "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}";
        }

        $this->settings->setMany([
            'cloudinary_cloud_name' => $cloudName ?: null,
            'cloudinary_api_key' => $apiKey ?: null,
            'cloudinary_url' => $cloudinaryUrl ?: null,
            'storage_disk' => 'cloudinary',
        ], 'storage');

        if (! empty($apiSecret)) {
            $this->settings->set('cloudinary_api_secret', $apiSecret, 'storage');
        }

        ActivityLog::record('settings.storage.cloudinary_updated', null, [
            'cloud_name' => $cloudName,
            'has_api_key' => ! empty($apiKey),
        ]);

        return back()->with('status', 'Cloudinary storage settings saved and set as Active Driver.');
    }

    public function updateR2Storage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'r2_access_key_id' => ['nullable', 'string', 'max:255'],
            'r2_secret_access_key' => ['nullable', 'string', 'max:255'],
            'r2_bucket' => ['nullable', 'string', 'max:255'],
            'r2_endpoint' => ['nullable', 'url', 'max:255'],
            'r2_url' => ['nullable', 'url', 'max:255'],
            'r2_region' => ['nullable', 'string', 'max:50'],
        ]);

        $this->settings->setMany([
            'r2_access_key_id' => $validated['r2_access_key_id'] ?? null,
            'r2_bucket' => $validated['r2_bucket'] ?? null,
            'r2_endpoint' => $validated['r2_endpoint'] ?? null,
            'r2_url' => $validated['r2_url'] ?? null,
            'r2_region' => $validated['r2_region'] ?? 'auto',
            'storage_disk' => 'r2',
        ], 'storage');

        if ($request->filled('r2_secret_access_key')) {
            $this->settings->set('r2_secret_access_key', $request->string('r2_secret_access_key')->value(), 'storage');
        }

        ActivityLog::record('settings.storage.r2_updated', null, [
            'bucket' => $validated['r2_bucket'] ?? null,
        ]);

        return back()->with('status', 'Cloudflare R2 storage settings saved and set as Active Driver.');
    }

    public function updateStorage(UpdateStorageRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'r2_access_key_id' => $request->string('r2_access_key_id')->value() ?: null,
            'r2_bucket' => $request->string('r2_bucket')->value() ?: null,
            'r2_endpoint' => $request->string('r2_endpoint')->value() ?: null,
            'r2_url' => $request->string('r2_url')->value() ?: null,
            'r2_region' => $request->string('r2_region')->value() ?: 'auto',
            'cloudinary_cloud_name' => $request->string('cloudinary_cloud_name')->value() ?: null,
            'cloudinary_api_key' => $request->string('cloudinary_api_key')->value() ?: null,
            'cloudinary_url' => $request->string('cloudinary_url')->value() ?: null,
            'storage_disk' => $request->string('storage_disk')->value(),
        ], 'storage');

        if ($request->filled('r2_secret_access_key')) {
            $this->settings->set('r2_secret_access_key', $request->string('r2_secret_access_key')->value(), 'storage');
        }

        if ($request->filled('cloudinary_api_secret')) {
            $this->settings->set('cloudinary_api_secret', $request->string('cloudinary_api_secret')->value(), 'storage');
        }

        ActivityLog::record('settings.storage.updated');

        return back()->with('status', 'Storage settings updated.');
    }

    public function testStorage(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $targetDisk = $request->input('disk', $this->settings->get('storage_disk', 'r2'));
        if (! in_array($targetDisk, ['r2', 'cloudinary'], true)) {
            $targetDisk = 'r2';
        }

        if ($targetDisk === 'cloudinary') {
            Storage::extend('cloudinary', function ($app, array $config) {
                $adapter = new \App\Support\CloudinaryAdapter($config);
                $flysystem = new \League\Flysystem\Filesystem($adapter, $config);

                return new \Illuminate\Filesystem\FilesystemAdapter($flysystem, $adapter, $config);
            });

            config([
                'filesystems.disks.cloudinary' => [
                    'driver' => 'cloudinary',
                    'cloud_name' => $this->settings->get('cloudinary_cloud_name', 'w36cggya'),
                    'api_key' => $this->settings->get('cloudinary_api_key', '146238954648692'),
                    'api_secret' => $this->settings->getDecrypted('cloudinary_api_secret') ?: 'BEuXx-vqfq-tPmWMTaRbJd6SyH8',
                    'url' => $this->settings->get('cloudinary_url', 'cloudinary://146238954648692:BEuXx-vqfq-tPmWMTaRbJd6SyH8@w36cggya'),
                ],
            ]);
        } elseif ($targetDisk === 'r2') {
            config([
                'filesystems.disks.r2' => [
                    'driver' => 's3',
                    'key' => $this->settings->get('r2_access_key_id'),
                    'secret' => $this->settings->has('r2_secret_access_key') ? $this->settings->getDecrypted('r2_secret_access_key') : null,
                    'region' => $this->settings->get('r2_region', 'auto'),
                    'bucket' => $this->settings->get('r2_bucket'),
                    'url' => $this->settings->get('r2_url'),
                    'endpoint' => $this->settings->get('r2_endpoint'),
                    'use_path_style_endpoint' => false,
                    'throw' => false,
                ],
            ]);
        }

        try {
            $path = 'openbox-connection-test.txt';
            Storage::disk($targetDisk)->put($path, 'Openbox '.$targetDisk.' connection test — '.now());
            Storage::disk($targetDisk)->delete($path);
        } catch (\Throwable $e) {
            $fieldKey = $targetDisk === 'cloudinary' ? 'cloudinary_cloud_name' : 'r2_access_key_id';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed: '.$e->getMessage(),
                ], 422);
            }

            return back()->withErrors([$fieldKey => 'Connection failed: '.$e->getMessage()]);
        }

        ActivityLog::record('settings.storage.tested', null, ['disk' => $targetDisk]);

        $diskLabel = $targetDisk === 'cloudinary' ? 'Cloudinary' : 'Cloudflare R2';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully connected to '.$diskLabel.'.',
            ]);
        }

        return back()->with('status', 'Successfully connected to '.$diskLabel.'.');
    }

    public function payments(): View
    {
        return view('admin.settings.payments', [
            'values' => [
                'stripe_enabled' => $this->settings->get('stripe_enabled') === '1',
                'stripe_publishable_key' => $this->settings->get('stripe_publishable_key'),
                'paypal_enabled' => $this->settings->get('paypal_enabled') === '1',
                'paypal_client_id' => $this->settings->get('paypal_client_id'),
                'bank_details' => $this->settings->get('bank_details'),
            ],
            'hasStripeSecret' => $this->settings->has('stripe_secret_key'),
            'hasPaypalSecret' => $this->settings->has('paypal_client_secret'),
        ]);
    }

    public function updatePayments(UpdatePaymentsRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'stripe_enabled' => $request->boolean('stripe_enabled') ? '1' : '0',
            'stripe_publishable_key' => $request->string('stripe_publishable_key')->value() ?: null,
            'paypal_enabled' => $request->boolean('paypal_enabled') ? '1' : '0',
            'paypal_client_id' => $request->string('paypal_client_id')->value() ?: null,
            'bank_details' => $request->string('bank_details')->value() ?: null,
        ], 'payments');

        if ($request->filled('stripe_secret_key')) {
            $this->settings->set('stripe_secret_key', $request->string('stripe_secret_key')->value(), 'payments');
        }

        if ($request->filled('paypal_client_secret')) {
            $this->settings->set('paypal_client_secret', $request->string('paypal_client_secret')->value(), 'payments');
        }

        ActivityLog::record('settings.payments.updated');

        return back()->with('status', 'Payment settings updated.');
    }
}
