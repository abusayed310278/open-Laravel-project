<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
    ) {}

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and authenticate.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'login' => 'Google sign-in was cancelled or failed. Please try again.',
            ]);
        }

        $sessionId = $request->session()->get('cart_session_id');

        // Look for existing user with this google_id or email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        $isNewUser = false;

        if ($user) {
            $updates = [];

            if (! $user->google_id) {
                $updates['google_id'] = $googleUser->getId();
            }

            if (! $user->avatar && $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }

            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }

            if (! empty($updates)) {
                $user->update($updates);
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => UserRole::Customer,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            $user->profile()->create();

            $isNewUser = true;

            try {
                event(new Registered($user));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Check if user is blocked or suspended
        if (in_array($user->status, [UserStatus::Blocked, UserStatus::Suspended], strict: true)) {
            return redirect()->route('login')->withErrors([
                'login' => 'Your account is '.$user->status->label().'. Please contact support.',
            ]);
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        // Merge guest cart into user account
        if ($sessionId) {
            $this->carts->mergeGuestCartIntoUser($sessionId, $user);
        }

        $dashboard = route($user->role->dashboardRoute(), absolute: false);
        $intended = $request->session()->get('url.intended');

        if (! $intended || $intended === url('/') || $intended === url('/login') || $intended === url('/register')) {
            return redirect()->to($dashboard);
        }

        return redirect()->intended($dashboard);
    }
}
