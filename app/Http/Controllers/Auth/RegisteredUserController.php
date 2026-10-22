<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegistrationService;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
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
    public function store(RegisterRequest $request): RedirectResponse|JsonResponse
    {
        $sessionId = $request->session()->get('cart_session_id');

        $user = $this->registrationService->register($request->validated());

        Auth::login($user);

        if ($sessionId) {
            $this->carts->mergeGuestCartIntoUser($sessionId, $user);
        }

        // Only dispatch Registered immediately for roles that do NOT have a post-registration onboarding flow.
        // Customer and Store Owner (Business/Saler) finish registration during their onboarding step.
        if (! in_array($user->role, [UserRole::Customer, UserRole::Business, UserRole::Saler], strict: true)) {
            try {
                event(new Registered($user));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $intended = $request->session()->get('url.intended');

        $nextRoute = match (true) {
            in_array($user->role, [UserRole::Business, UserRole::Saler], strict: true) => route('onboarding.profile'),
            $user->role === UserRole::Customer => route('onboarding.address'),
            default => route($user->role->dashboardRoute()),
        };

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $intended ?: $nextRoute,
            ]);
        }

        if ($intended && $intended !== url('/') && $intended !== url('/register')) {
            return redirect()->intended();
        }

        return redirect()->to($nextRoute);
    }
}
