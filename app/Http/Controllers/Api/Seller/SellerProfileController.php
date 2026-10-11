<?php

namespace App\Http\Controllers\Api\Seller;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\SellerUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SellerProfileController extends Controller
{
    /**
     * Get store settings and profile.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'profile' => new SellerUserResource($user),
        ]);
    }

    /**
     * Update store settings, profile, logo, and cover banner.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_slug' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable'],
            'longitude' => ['nullable'],
            'website' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'is_store_active' => ['nullable'],
            'business_hours' => ['nullable'],
            'social_links' => ['nullable'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
            'avatar' => ['nullable', 'file', 'image', 'max:5120'],
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
            'cover_image' => ['nullable', 'file', 'image', 'max:6144'],
        ]);

        if (! empty($validated['name'])) {
            $user->update(['name' => $validated['name']]);
        }
        if (isset($validated['phone'])) {
            $user->update(['phone' => $validated['phone']]);
        }

        $photoFile = $request->file('photo') ?? $request->file('avatar') ?? $request->file('logo');
        $photoPath = null;
        if ($photoFile) {
            $photoPath = FileUploadHelper::store($photoFile, 'avatars', 'public');
        }

        // Update UserProfile (user_profiles table)
        $userProfileData = [];
        if (isset($validated['company'])) {
            $userProfileData['company'] = $validated['company'];
        }
        if (isset($validated['location'])) {
            $userProfileData['location'] = $validated['location'];
        }
        if ($photoPath) {
            $userProfileData['avatar'] = $photoPath;
        }
        if (! empty($userProfileData)) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $userProfileData);
        }

        $slugValue = $validated['store_slug'] ?? ($validated['slug'] ?? null);

        $businessHours = null;
        if ($request->has('business_hours')) {
            $rawHours = $request->input('business_hours');
            $businessHours = is_array($rawHours) ? $rawHours : json_decode((string) $rawHours, true);
        }

        $socialLinks = null;
        if ($request->has('social_links')) {
            $rawLinks = $request->input('social_links');
            $socialLinks = is_array($rawLinks) ? $rawLinks : json_decode((string) $rawLinks, true);
            if (is_array($socialLinks)) {
                $socialLinks = \App\Support\SocialLinkNormalizer::forStorage($socialLinks);
            }
        }

        if ($user->isBusiness()) {
            $profile = $user->businessProfile ?? $user->businessProfile()->create([
                'business_name' => $validated['company'] ?? ($validated['store_name'] ?? $user->name),
                'slug' => Str::slug($slugValue ?? ($validated['company'] ?? ($validated['store_name'] ?? $user->name))) ?: 'store-' . $user->id,
            ]);

            $attributes = [];
            if (! empty($validated['store_name'])) {
                $attributes['business_name'] = $validated['store_name'];
            }
            if (! empty($validated['company'])) {
                $attributes['business_name'] = $validated['company'];
            }
            if ($slugValue !== null) {
                $attributes['slug'] = Str::slug($slugValue) ?: 'store-' . $user->id;
            }
            if (isset($validated['description'])) {
                $attributes['description'] = $validated['description'];
            }
            if (isset($validated['bio'])) {
                $attributes['description'] = $validated['bio'];
            }
            if (isset($validated['address'])) {
                $attributes['address'] = $validated['address'];
            }
            if (isset($validated['location'])) {
                $attributes['address'] = $validated['location'];
            }
            if (isset($validated['city'])) {
                $attributes['city'] = $validated['city'];
            }
            if (isset($validated['country'])) {
                $attributes['country'] = $validated['country'];
            }
            if (array_key_exists('latitude', $validated)) {
                $attributes['latitude'] = (is_numeric($validated['latitude'])) ? (float) $validated['latitude'] : null;
            }
            if (array_key_exists('longitude', $validated)) {
                $attributes['longitude'] = (is_numeric($validated['longitude'])) ? (float) $validated['longitude'] : null;
            }
            if (isset($validated['timezone'])) {
                $attributes['timezone'] = $validated['timezone'];
            }
            if (isset($validated['website'])) {
                $attributes['website'] = $validated['website'];
            }
            if (isset($validated['phone'])) {
                $attributes['phone'] = $validated['phone'];
            }
            if ($request->has('is_store_active')) {
                $attributes['is_store_active'] = $request->boolean('is_store_active');
            }
            if ($businessHours !== null) {
                $attributes['business_hours'] = $businessHours;
            }
            if ($socialLinks !== null) {
                $attributes['social_links'] = $socialLinks;
            }
            if ($photoPath) {
                $attributes['logo'] = $photoPath;
            }
            if ($request->hasFile('cover_image')) {
                $attributes['cover_image'] = FileUploadHelper::store($request->file('cover_image'), 'stores/covers', 'public');
            }

            if (! empty($attributes)) {
                $profile->update($attributes);
            }
        } else {
            $profile = $user->salerProfile ?? $user->salerProfile()->create([
                'display_name' => $validated['company'] ?? ($validated['store_name'] ?? $user->name),
                'slug' => Str::slug($slugValue ?? ($validated['company'] ?? ($validated['store_name'] ?? $user->name))) ?: 'seller-' . $user->id,
            ]);

            $attributes = [];
            if (! empty($validated['store_name'])) {
                $attributes['display_name'] = $validated['store_name'];
            }
            if (! empty($validated['company'])) {
                $attributes['display_name'] = $validated['company'];
            }
            if ($slugValue !== null) {
                $attributes['slug'] = Str::slug($slugValue) ?: 'seller-' . $user->id;
            }
            if (isset($validated['description'])) {
                $attributes['bio'] = $validated['description'];
            }
            if (isset($validated['bio'])) {
                $attributes['bio'] = $validated['bio'];
            }
            if (isset($validated['address'])) {
                $attributes['location'] = $validated['address'];
            }
            if (isset($validated['location'])) {
                $attributes['location'] = $validated['location'];
            }
            if (isset($validated['city'])) {
                $attributes['city'] = $validated['city'];
            }
            if (isset($validated['country'])) {
                $attributes['country'] = $validated['country'];
            }
            if (array_key_exists('latitude', $validated)) {
                $attributes['latitude'] = (is_numeric($validated['latitude'])) ? (float) $validated['latitude'] : null;
            }
            if (array_key_exists('longitude', $validated)) {
                $attributes['longitude'] = (is_numeric($validated['longitude'])) ? (float) $validated['longitude'] : null;
            }
            if (isset($validated['timezone'])) {
                $attributes['timezone'] = $validated['timezone'];
            }
            if ($request->has('is_store_active')) {
                $attributes['is_store_active'] = $request->boolean('is_store_active');
            }
            if ($businessHours !== null) {
                $attributes['business_hours'] = $businessHours;
            }
            if ($socialLinks !== null) {
                $attributes['social_links'] = $socialLinks;
            }
            if ($photoPath) {
                $attributes['profile_photo'] = $photoPath;
            }
            if ($request->hasFile('cover_image')) {
                $attributes['cover_image'] = FileUploadHelper::store($request->file('cover_image'), 'sellers/covers', 'public');
            }

            if (! empty($attributes)) {
                $profile->update($attributes);
            }
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => new SellerUserResource($user->fresh()),
        ]);
    }
}
