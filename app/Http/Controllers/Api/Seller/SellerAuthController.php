<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SellerUserResource;
use App\Models\User;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SellerAuthController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService,
    ) {}

    /**
     * Register a new Store Owner account and return Sanctum access token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'business_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->registrationService->register([
            'account_type' => UserRole::Business->value,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'business_name' => $validated['business_name'],
        ]);

        $token = $user->createToken('flutter-mobile-seller-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => new SellerUserResource($user),
        ], 201);
    }

    /**
     * Authenticate seller account and return Sanctum access token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Enforce that only Sellers (saler) or Store Owners (business) can access the seller app
        if (! $user->isSaler() && ! $user->isBusiness()) {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. This app is strictly for Sellers and Store Owners.',
            ], 403);
        }

        if ($user->status === UserStatus::Suspended || $user->status === UserStatus::Blocked) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been restricted. Please contact support.',
            ], 403);
        }

        $token = $user->createToken('flutter-mobile-seller-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new SellerUserResource($user),
        ]);
    }

    /**
     * Get authenticated seller profile and store state.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => new SellerUserResource($user),
        ]);
    }

    /**
     * Revoke current session token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
/**
     * Change authenticated seller password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password does not match our records.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
