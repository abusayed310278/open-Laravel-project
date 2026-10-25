<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();

        return response()->json([
            'addresses' => $addresses->map(fn (Address $a) => [
                'id' => (string) $a->id,
                'label' => $a->label ?? 'Primary',
                'name' => $a->name,
                'recipient' => $a->name,
                'phone' => $a->phone,
                'line1' => $a->line1,
                'line2' => $a->line2,
                'city' => $a->city,
                'state' => $a->state ?? '',
                'country' => $a->country ?? '',
                'postal_code' => $a->postal_code ?? '',
                'is_default' => (bool) $a->is_default,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $name = $validated['name'] ?? ($validated['recipient'] ?? ($user->name ?? 'Primary Contact'));
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'label' => $validated['label'] ?? 'Primary',
            'name' => $name,
            'phone' => $validated['phone'],
            'line1' => $validated['line1'],
            'line2' => $validated['line2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? 'Qatar',
            'postal_code' => $validated['postal_code'] ?? null,
            'is_default' => $isDefault,
        ]);

        // Generate and send 6-digit email verification OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            Mail::raw(
                "Hello {$user->name},\n\nYour OpenBox email verification code is: {$otp}\n\nPlease enter this OTP in your mobile app to verify your account.\n\nThank you,\nOpenBox Team",
                function ($message) use ($user) {
                    $message->to($user->email)->subject("OpenBox - Email Verification Code");
                }
            );
        } catch (\Throwable $e) {
            \Log::error("Failed to send onboarding email verification OTP to {$user->email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Address saved successfully. A verification code has been sent to your email.',
            'email' => $user->email,
            'address' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'name' => $address->name,
                'recipient' => $address->name,
                'phone' => $address->phone,
                'line1' => $address->line1,
                'line2' => $address->line2,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'postal_code' => $address->postal_code,
                'is_default' => (bool) $address->is_default,
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            $user->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $name = $validated['name'] ?? ($validated['recipient'] ?? $address->name);

        $address->update([
            'label' => $validated['label'] ?? $address->label ?? 'Primary',
            'name' => $name,
            'phone' => $validated['phone'],
            'line1' => $validated['line1'],
            'line2' => $validated['line2'] ?? $address->line2,
            'city' => $validated['city'],
            'state' => $validated['state'] ?? $address->state,
            'country' => $validated['country'] ?? $address->country,
            'postal_code' => $validated['postal_code'] ?? $address->postal_code,
            'is_default' => $isDefault ? true : $address->is_default,
        ]);

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'name' => $address->name,
                'recipient' => $address->name,
                'phone' => $address->phone,
                'line1' => $address->line1,
                'line2' => $address->line2,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'postal_code' => $address->postal_code,
                'is_default' => (bool) $address->is_default,
            ],
        ]);
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $user->addresses()->update(['is_default' => false]);
        $user->addresses()->where('id', $id)->update(['is_default' => true]);

        return response()->json(['message' => 'Default address updated']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->where('id', $id)->first();
        if ($address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault && $user->addresses()->exists()) {
                $user->addresses()->first()->update(['is_default' => true]);
            }
        }

        return response()->json(['message' => 'Address deleted successfully']);
    }
}