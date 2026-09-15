<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $sessionId = $request->session()->get('cart_session_id');

        $request->authenticate();

        $request->session()->regenerate();

        $request->user()->forceFill(['last_login_at' => now()])->save();

        if ($sessionId) {
            $this->carts->mergeGuestCartIntoUser($sessionId, $request->user());
        }

        $intended = $request->session()->get('url.intended');
        $dashboard = route($request->user()->role->dashboardRoute(), absolute: false);

        if (! $intended || $intended === url('/') || $intended === url('/login')) {
            return redirect()->to($dashboard);
        }

        return redirect()->intended($dashboard);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
