<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function edit(): View
    {
        return view('account.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
        ]);

        $emailChanged = $data['email'] !== $user->email;

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['avatar' => $avatarPath]
            );
        }

        if ($user->businessProfile && !empty($data['company'])) {
            $user->businessProfile->update(['business_name' => $data['company']]);
        }
        if ($user->salerProfile) {
            if (!empty($data['company'])) {
                $user->salerProfile->update(['display_name' => $data['company']]);
            }
            if (!empty($data['location'])) {
                $user->salerProfile->update(['location' => $data['location']]);
            }
        }

        // email_verified_at is intentionally not fillable (see .ai/rules/http-controllers.md), so it needs a separate forced write.
        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
        }

        return back()->with('status', 'Profile updated.');
    }
}
