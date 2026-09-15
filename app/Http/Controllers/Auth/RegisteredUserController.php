<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegistrationService;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly CartService $carts,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $sessionId = $request->session()->get('cart_session_id');

        $user = $this->registrationService->register($request->validated());

        event(new Registered($user));

        Auth::login($user);

        if ($sessionId) {
            $this->carts->mergeGuestCartIntoUser($sessionId, $user);
        }

        if (in_array($user->role, [UserRole::Business, UserRole::Saler], strict: true)) {
            return redirect()->route('onboarding.profile');
        }

        return redirect()->route($user->role->dashboardRoute());
    }
}
