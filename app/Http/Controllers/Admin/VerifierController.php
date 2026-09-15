<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerifierRequest;
use App\Models\User;
use App\Models\VerificationLocation;
use App\Models\VerifierProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VerifierController extends Controller
{
    public function index(): View
    {
        return view('admin.verifiers.index', [
            'verifiers' => User::query()->where('role', UserRole::Verifier)->with('verifierProfile.location')->orderBy('name')->get(),
            'locations' => VerificationLocation::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreVerifierRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => $request->string('name')->value(),
                'email' => $request->string('email')->value(),
                'password' => Hash::make(Str::random(24)),
                'role' => UserRole::Verifier,
                'status' => UserStatus::Active,
            ]);

            // Admin-created accounts are trusted immediately — no self-service
            // verification link to click. "email_verified_at" isn't mass
            // assignable, so it has to be set explicitly here.
            $user->forceFill(['email_verified_at' => now()])->save();

            $user->verifierProfile()->create([
                'employee_id' => $request->string('employee_id')->value(),
                'assigned_location_id' => $request->integer('assigned_location_id') ?: null,
            ]);
        });

        return back()->with('status', 'Verifier account created. Ask them to use "Forgot password" to set their own password.');
    }

    public function assignLocation(VerifierProfile $verifierProfile): RedirectResponse
    {
        $verifierProfile->update([
            'assigned_location_id' => request()->integer('assigned_location_id') ?: null,
        ]);

        return back()->with('status', 'Verifier location updated.');
    }
}
