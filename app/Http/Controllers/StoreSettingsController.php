<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessStoreRequest;
use App\Http\Requests\UpdateSalerStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        return view('seller.store.edit', [
            'profile' => $user->isBusiness() ? $user->businessProfile : $user->salerProfile,
        ]);
    }

    public function updateBusiness(UpdateBusinessStoreRequest $request): RedirectResponse
    {
        $profile = $request->user()->businessProfile;
        $data = $request->safe()->except(['logo', 'cover_image']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('business-logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('business-covers', 'public');
        }

        $profile->update($data);

        return back()->with('status', 'Store profile updated.');
    }

    public function updateSaler(UpdateSalerStoreRequest $request): RedirectResponse
    {
        $profile = $request->user()->salerProfile;
        $data = $request->safe()->except(['profile_photo', 'cover_image']);

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('saler-photos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('saler-covers', 'public');
        }

        $profile->update($data);

        return back()->with('status', 'Store profile updated.');
    }
}
