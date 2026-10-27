<?php

namespace App\Http\Controllers\Api\Seller;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\SellerUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $resource = new SellerUserResource($user);

        return response()->json([
            'profile' => $resource,
            'user' => $resource,
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
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
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
            'photo' => ['nullable', 'file', 'image', 'max:15360'],
            'avatar' => ['nullable', 'file', 'image', 'max:15360'],
            'logo' => ['nullable', 'file', 'image', 'max:15360'],
            'profile_photo' => ['nullable', 'file', 'image', 'max:15360'],
            'image' => ['nullable', 'file', 'image', 'max:15360'],
            'cover_image' => ['nullable', 'file', 'image', 'max:15360'],
            'cover' => ['nullable', 'file', 'image', 'max:15360'],
            'banner' => ['nullable', 'file', 'image', 'max:15360'],
            'banner_image' => ['nullable', 'file', 'image', 'max:15360'],
            'store_banner' => ['nullable', 'file', 'image', 'max:15360'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_photo' => ['nullable', 'boolean'],
            'remove_cover' => ['nullable', 'boolean'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['email'])) {
            $user->update(['email' => $validated['email']]);
        }

        if (! empty($validated['name'])) {
            $user->update(['name' => $validated['name']]);
        }
        if (isset($validated['phone'])) {
            $user->update(['phone' => $validated['phone']]);
        }

        $photoFile = $request->file('logo')
            ?? $request->file('photo')
            ?? $request->file('avatar')
            ?? $request->file('profile_photo')
            ?? $request->file('image');

        $coverFile = $request->file('cover_image')
            ?? $request->file('cover')
            ?? $request->file('banner')
            ?? $request->file('banner_image')
            ?? $request->file('store_banner');

        $photoPath = null;
        if ($photoFile && $photoFile->isValid()) {
            $photoPath = FileUploadHelper::store($photoFile, 'avatars');
        }

        $coverPath = null;
        if ($coverFile && $coverFile->isValid()) {
            $coverPath = FileUploadHelper::store($coverFile, 'sellers/covers');
        }

        $removeLogo = $request->boolean('remove_logo') || $request->boolean('remove_photo');
        $removeCover = $request->boolean('remove_cover') || $request->boolean('remove_cover_image') || $request->boolean('remove_banner');

        // Update User & UserProfile
        if ($photoPath) {
            $user->update(['avatar' => $photoPath]);
        } elseif ($removeLogo) {
            $user->update(['avatar' => null]);
        }

        $userProfileData = [];
        if (isset($validated['company'])) {
            $userProfileData['company'] = $validated['company'];
        }
        if (isset($validated['location'])) {
            $userProfileData['location'] = $validated['location'];
        }
        if ($photoPath) {
            $userProfileData['avatar'] = $photoPath;
        } elseif ($removeLogo) {
            $userProfileData['avatar'] = null;
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
            } elseif ($removeLogo) {
                $attributes['logo'] = null;
            }
            if ($coverPath) {
                $attributes['cover_image'] = $coverPath;
            } elseif ($removeCover) {
                $attributes['cover_image'] = null;
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
            } elseif ($removeLogo) {
                $attributes['profile_photo'] = null;
            }
            if ($coverPath) {
                $attributes['cover_image'] = $coverPath;
            } elseif ($removeCover) {
                $attributes['cover_image'] = null;
            }

            if (! empty($attributes)) {
                $profile->update($attributes);
            }
        }

        $user->refresh();
        $user->loadMissing(['businessProfile', 'salerProfile', 'profile', 'activeSubscription', 'wallet']);

        $resource = new SellerUserResource($user);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $resource,
            'user' => $resource,
        ]);
    }
}