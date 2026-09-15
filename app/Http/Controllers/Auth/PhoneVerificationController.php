<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneVerificationController extends Controller
{
    public function __construct(private readonly PhoneVerificationService $phoneVerification) {}

    public function notice(): View
    {
        return view('auth.verify-phone');
    }

    public function send(Request $request): RedirectResponse
    {
        abort_unless($request->user()->phone, 422, 'Add a phone number to your account first.');

        $this->phoneVerification->send($request->user());

        return back()->with('status', 'otp-sent');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        if (! $this->phoneVerification->verify($request->user(), $request->string('code')->value())) {
            return back()->withErrors(['code' => 'That code is incorrect or has expired.']);
        }

        return redirect()->route($request->user()->role->dashboardRoute())
            ->with('status', 'Phone number verified.');
    }
}
