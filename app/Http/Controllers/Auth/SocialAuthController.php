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
use Laravel\Socialite\Contracts\User as SocialiteUser;
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

        return $this->authenticateSocialUser($request, 'google', $googleUser, 'google_id');
    }

    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirectToFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')->scopes(['email'])->redirect();
    }

    /**
     * Obtain the user information from Facebook and authenticate.
     */
    public function handleFacebookCallback(Request $request): RedirectResponse
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'login' => 'Facebook sign-in was cancelled or failed. Please try again.',
            ]);
        }

        return $this->authenticateSocialUser($request, 'facebook', $facebookUser, 'facebook_id');
    }

    /**
     * Authenticate or register the social user.
     */
    private function authenticateSocialUser(
        Request $request,
        string $provider,
        SocialiteUser $socialUser,
        string $idColumn
    ): RedirectResponse {
        $sessionId = $request->session()->get('cart_session_id');
        $email = $socialUser->getEmail() ?: ($provider.'_'.$socialUser->getId().'@'.$provider.'.com');

        // Look for existing user with this provider id or email
        $user = User::where($idColumn, $socialUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $updates = [];

            if (! $user->{$idColumn}) {
                $updates[$idColumn] = $socialUser->getId();
            }

            if (! $user->avatar && $socialUser->getAvatar()) {
                $updates['avatar'] = $socialUser->getAvatar();
            }

            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }

            if (! empty($updates)) {
                $user->update($updates);
            }
        } else {
            $user = User::create([
                'name' => $socialUser->getName() ?: 'User',
                'email' => $email,
                $idColumn => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'role' => UserRole::Customer,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            $user->profile()->create();

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
