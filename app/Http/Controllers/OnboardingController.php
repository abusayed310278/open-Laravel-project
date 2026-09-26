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

        $requirements = app(\App\Services\KycService::class)->requirementsFor($user);

        return view('onboarding.profile', [
            'profile' => $profile,
            'requirements' => $requirements,
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

        $validated = $request->validated();

        $data = $user->role === UserRole::Business
            ? [
                'description' => $validated['description'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'website' => $validated['website'] ?? null,
            ]
            : [
                'bio' => $validated['description'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
            ];

        // Handle Store Logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file && $file->isValid() && filled($file->getRealPath()) && file_exists($file->getRealPath())) {
                $path = \App\Helpers\FileUploadHelper::store(
                    $file,
                    $user->role === UserRole::Business ? 'business-logos' : 'saler-photos'
                );

                if ($path) {
                    $data[$user->role === UserRole::Business ? 'logo' : 'profile_photo'] = $path;
                }
            }
        }

        // Handle Cover Photo
        if ($request->hasFile('cover_image')) {
            $cFile = $request->file('cover_image');
            if ($cFile && $cFile->isValid() && filled($cFile->getRealPath()) && file_exists($cFile->getRealPath())) {
                $cPath = \App\Helpers\FileUploadHelper::store(
                    $cFile,
                    $user->role === UserRole::Business ? 'business-covers' : 'saler-covers'
                );

                if ($cPath) {
                    $data['cover_image'] = $cPath;
                }
            }
        }

        $profile->update([...$data, 'profile_completed' => true]);

        // Process KYC Document Uploads
        $kycFiles = $request->file('kyc_documents', []);
        if (is_array($kycFiles) && count(array_filter($kycFiles)) > 0) {
            app(\App\Services\KycService::class)->submit($user, array_filter($kycFiles));
        }

        return redirect()->route($user->role->dashboardRoute())
            ->with('status', 'Your store profile is all set up and verification submitted.');
    }

    /**
     * Show the address collection onboarding view for Customer/User accounts.
     */
    public function address(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Customer) {
            return redirect()->route($user->role->dashboardRoute());
        }

        if ($user->addresses()->exists()) {
            return redirect()->route('verification.notice');
        }

        return view('onboarding.address', [
            'user' => $user,
        ]);
    }

    /**
     * Store the customer onboarding shipping address and send email verification link/code.
     */
    public function storeAddress(\Illuminate\Http\Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->role === UserRole::Customer, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $user->addresses()->create([
            ...$validated,
            'label' => 'Primary',
            'is_default' => true,
        ]);

        try {
            event(new \Illuminate\Auth\Events\Registered($user));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('verification.notice')
            ->with('status', 'Address saved successfully! A verification link has been sent to your email.');
    }
}
