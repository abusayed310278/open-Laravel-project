<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerSecurityController extends Controller
{
    public function edit(): View
    {
        return view('account.security.edit');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // User::$password has a 'hashed' cast, so the plain value is hashed automatically on save.
        Auth::user()->update(['password' => $data['password']]);

        return back()->with('status', 'Password updated.');
    }
}
