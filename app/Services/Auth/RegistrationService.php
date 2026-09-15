<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\BusinessProfile;
use App\Models\SalerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationService
{
    /**
     * Create a user for the given account type and its role-specific profile.
     *
     * @param  array{account_type: string, name: string, email: string, phone: ?string, password: string, business_name?: string, display_name?: string, location?: string}  $data
     */
    public function register(array $data): User
    {
        $role = UserRole::from($data['account_type']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $role,
            'status' => UserStatus::Active,
        ]);

        match ($role) {
            UserRole::Business => $this->createBusinessProfile($user, $data['business_name']),
            UserRole::Saler => $this->createSalerProfile($user, $data['display_name'], $data['location']),
            default => null,
        };

        return $user;
    }

    private function createBusinessProfile(User $user, string $businessName): BusinessProfile
    {
        return BusinessProfile::create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'slug' => $this->uniqueSlug(BusinessProfile::class, $businessName),
        ]);
    }

    private function createSalerProfile(User $user, string $displayName, string $location): SalerProfile
    {
        return SalerProfile::create([
            'user_id' => $user->id,
            'display_name' => $displayName,
            'location' => $location,
            'slug' => $this->uniqueSlug(SalerProfile::class, $displayName),
        ]);
    }

    /**
     * @param  class-string<BusinessProfile|SalerProfile>  $model
     */
    private function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while ($model::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
