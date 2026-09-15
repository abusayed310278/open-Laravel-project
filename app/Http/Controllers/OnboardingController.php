<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Onboarding\CompleteProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the "finish setting up your store" form for a newly registered
     * business or saler account.
     */
    public function profile(): View
    {
        return view('onboarding.profile', [
            'profile' => Auth::user()->role === UserRole::Business
                ? Auth::user()->businessProfile
                : Auth::user()->salerProfile,
        ]);
    }

    public function updateProfile(CompleteProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->role === UserRole::Business ? $user->businessProfile : $user->salerProfile;

        $validated = $request->safe()->except('logo');

        $data = $user->role === UserRole::Business
            ? $validated
            : ['bio' => $validated['description'], 'city' => $validated['city'], 'country' => $validated['country']];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store(
                $user->role === UserRole::Business ? 'business-logos' : 'saler-photos',
                'public',
            );

            $data[$user->role === UserRole::Business ? 'logo' : 'profile_photo'] = $path;
        }

        $profile->update([...$data, 'profile_completed' => true]);

        return redirect()->route($user->role->dashboardRoute())
            ->with('status', 'Your store profile is all set up.');
    }
}
