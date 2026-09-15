<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Redirect business/saler accounts to the onboarding form until they've
     * completed their store profile (business name is set at registration,
     * but logo/address/etc. are collected right after).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profile = match ($user->role) {
            UserRole::Business => $user->businessProfile,
            UserRole::Saler => $user->salerProfile,
            default => null,
        };

        if ($profile && ! $profile->profile_completed && ! $request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.profile');
        }

        return $next($request);
    }
}
