<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Http\Requests\UpdateBusinessStoreRequest;
use App\Http\Requests\UpdateSalerStoreRequest;
use App\Models\SocialType;
use App\Support\SocialLinkNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    private const array WEEK_DAYS = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
    ];

    public function edit(): View
    {
        $user = Auth::user();

        if ($user->isBusiness()) {
            $profile = $user->businessProfile ?? $user->businessProfile()->create([
                'business_name' => $user->name,
                'slug' => Str::slug($user->name) ?: 'store-' . $user->id,
            ]);
        } else {
            $profile = $user->salerProfile ?? $user->salerProfile()->create([
                'display_name' => $user->name,
                'slug' => Str::slug($user->name) ?: 'seller-' . $user->id,
            ]);
        }

        $socialTypes = SocialType::active()->ordered()->get();
        $kycService = app(\App\Services\KycService::class);
        $kycApplication = $kycService->currentApplication($user)->load('documents');
        $kycRequirements = $kycService->requirementsFor($user);

        return view('seller.store.edit', [
            'profile' => $profile,
            'socialPlatformOptions' => $socialTypes->map(fn (SocialType $type) => [
                'slug' => $type->slug,
                'name' => $type->name,
                'icon_svg' => $type->icon_svg,
            ])->values()->all(),
            'timezoneOptions' => $this->timezoneOptions(),
            'kycApplication' => $kycApplication,
            'kycRequirements' => $kycRequirements,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function timezoneOptions(): array
    {
        $options = [];

        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $offset = (new \DateTimeZone($identifier))->getOffset(new \DateTime('now', new \DateTimeZone($identifier)));
            $hours = intdiv(abs($offset), 3600);
            $minutes = intdiv(abs($offset) % 3600, 60);
            $sign = $offset >= 0 ? '+' : '-';
            $suffix = $minutes ? sprintf(':%02d', $minutes) : '';

            $options[$identifier] = sprintf('%s (GMT%s%d%s)', $identifier, $sign, $hours, $suffix);
        }

        return $options;
    }

    public function updateBusiness(UpdateBusinessStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->businessProfile ?? $user->businessProfile()->create([
            'business_name' => $user->name,
            'slug' => Str::slug($user->name) ?: 'store-' . $user->id,
        ]);

        $data = $request->safe()->except(['logo', 'cover_image', 'business_hours', 'social_links']);
        $data['is_store_active'] = $request->boolean('is_store_active');
        $data['profile_completed'] = true;
        $data['business_hours'] = $this->buildBusinessHours($request);
        $data['social_links'] = SocialLinkNormalizer::forStorage($request->input('social_links', []));

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $data['logo'] = FileUploadHelper::store($request->file('logo'), 'business-logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $data['logo'] = null;
        }

        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $data['cover_image'] = FileUploadHelper::store($request->file('cover_image'), 'business-covers', 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            $data['cover_image'] = null;
        }

        $profile->update($data);

        return back()->with('status', 'Store profile updated.');
    }

    public function updateSaler(UpdateSalerStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->salerProfile ?? $user->salerProfile()->create([
            'display_name' => $user->name,
            'slug' => Str::slug($user->name) ?: 'seller-' . $user->id,
        ]);

        $data = $request->safe()->except(['profile_photo', 'cover_image', 'business_hours', 'social_links']);
        $data['is_store_active'] = $request->boolean('is_store_active');
        $data['profile_completed'] = true;
        $data['business_hours'] = $this->buildBusinessHours($request);
        $data['social_links'] = SocialLinkNormalizer::forStorage($request->input('social_links', []));

        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            $data['profile_photo'] = FileUploadHelper::store($request->file('profile_photo'), 'saler-photos', 'public');
        } elseif ($request->boolean('remove_profile_photo')) {
            $data['profile_photo'] = null;
        }

        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $data['cover_image'] = FileUploadHelper::store($request->file('cover_image'), 'saler-covers', 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            $data['cover_image'] = null;
        }

        $profile->update($data);

        return back()->with('status', 'Store profile updated.');
    }

    /**
     * @return array<string, array{enabled: bool, open: ?string, close: ?string}>
     */
    private function buildBusinessHours(Request $request): array
    {
        $input = $request->input('business_hours', []);
        $hours = [];

        foreach (self::WEEK_DAYS as $day) {
            $row = is_array($input[$day] ?? null) ? $input[$day] : [];

            $hours[$day] = [
                'enabled' => (bool) ($row['enabled'] ?? false),
                'open' => $row['open'] ?? null,
                'close' => $row['close'] ?? null,
            ];
        }

        return $hours;
    }
}
