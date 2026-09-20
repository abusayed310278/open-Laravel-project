<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessStoreRequest;
use App\Http\Requests\UpdateSalerStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
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

        return view('seller.store.edit', [
            'profile' => $profile,
        ]);
    }

    public function updateBusiness(UpdateBusinessStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->businessProfile ?? $user->businessProfile()->create([
            'business_name' => $user->name,
            'slug' => Str::slug($user->name) ?: 'store-' . $user->id,
        ]);

        $data = $request->safe()->except(['logo', 'cover_image']);
        $data['is_store_active'] = $request->boolean('is_store_active');
        $data['profile_completed'] = true;

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $data['logo'] = \App\Helpers\FileUploadHelper::store($request->file('logo'), 'business-logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $data['logo'] = null;
        }

        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $data['cover_image'] = \App\Helpers\FileUploadHelper::store($request->file('cover_image'), 'business-covers', 'public');
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

        $data = $request->safe()->except(['profile_photo', 'cover_image']);
        $data['is_store_active'] = $request->boolean('is_store_active');
        $data['profile_completed'] = true;

        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            $data['profile_photo'] = \App\Helpers\FileUploadHelper::store($request->file('profile_photo'), 'saler-photos', 'public');
        } elseif ($request->boolean('remove_profile_photo')) {
            $data['profile_photo'] = null;
        }

        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $data['cover_image'] = \App\Helpers\FileUploadHelper::store($request->file('cover_image'), 'saler-covers', 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            $data['cover_image'] = null;
        }

        $profile->update($data);

        return back()->with('status', 'Store profile updated.');
    }
}

