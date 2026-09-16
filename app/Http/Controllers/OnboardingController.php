<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Onboarding\CompleteProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the "finish setting up your store" form for a newly registered
     * business or saler account.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $profile = $user->role === UserRole::Business
            ? ($user->businessProfile ?? $user->businessProfile()->create(['business_name' => $user->name, 'slug' => Str::slug($user->name)]))
            : ($user->salerProfile ?? $user->salerProfile()->create(['display_name' => $user->name, 'slug' => Str::slug($user->name)]));

        return view('onboarding.profile', [
            'profile' => $profile,
        ]);
    }

    public function updateProfile(CompleteProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->role === UserRole::Business ? $user->businessProfile : $user->salerProfile;

        if (! $profile) {
            $profile = $user->role === UserRole::Business
                ? $user->businessProfile()->create(['business_name' => $user->name, 'slug' => Str::slug($user->name)])
                : $user->salerProfile()->create(['display_name' => $user->name, 'slug' => Str::slug($user->name)]);
        }

        $validated = $request->safe()->except('logo');

        $data = $user->role === UserRole::Business
            ? $validated
            : ['bio' => $validated['description'], 'city' => $validated['city'], 'country' => $validated['country']];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file && $file->isValid() && filled($file->getRealPath()) && file_exists($file->getRealPath())) {
                $path = $file->store(
                    $user->role === UserRole::Business ? 'business-logos' : 'saler-photos',
                    'public',
                );

                if ($path) {
                    $data[$user->role === UserRole::Business ? 'logo' : 'profile_photo'] = $path;
                }
            }
        }

        $profile->update([...$data, 'profile_completed' => true]);

        return redirect()->route($user->role->dashboardRoute())
            ->with('status', 'Your store profile is all set up.');
    }
}
