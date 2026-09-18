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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class VerifierController extends Controller
{
    public function index(): View
    {
        return view('admin.verifiers.index', [
            'verifiers' => User::query()
                ->where('role', UserRole::Verifier)
                ->with('verifierProfile.location')
                ->orderBy('name')
                ->simplePaginate(15),
            'locations' => VerificationLocation::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreVerifierRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => $request->string('name')->value(),
                'email' => $request->string('email')->value(),
                'password' => Hash::make($request->string('password')->value()),
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

        return back()->with('status', 'Verifier account created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Verifier, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('verifier_profiles', 'employee_id')->ignore($user->verifierProfile?->id)],
            'assigned_location_id' => ['nullable', 'integer', 'exists:verification_locations,id'],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $user->verifierProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $validated['employee_id'],
                    'assigned_location_id' => $validated['assigned_location_id'] ?: null,
                ]
            );
        });

        return back()->with('status', 'Verifier updated successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Verifier, 404);

        $newStatus = $user->status === UserStatus::Active
            ? UserStatus::Suspended
            : UserStatus::Active;

        $user->update(['status' => $newStatus]);

        return back()->with('status', "Verifier status updated to {$newStatus->label()}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Verifier, 404);

        $user->delete();

        return back()->with('status', 'Verifier account removed.');
    }

    public function assignLocation(VerifierProfile $verifierProfile): RedirectResponse
    {
        $verifierProfile->update([
            'assigned_location_id' => request()->integer('assigned_location_id') ?: null,
        ]);

        return back()->with('status', 'Verifier location updated.');
    }
}

