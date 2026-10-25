<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SellerUserResource;
use App\Models\User;
use App\Services\Auth\PhoneVerificationService;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class SellerAuthController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly PhoneVerificationService $phoneVerification,
    ) {}

    /**
     * Register a new Seller / Store Owner / Customer account and return Sanctum access token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'in:business,saler,customer'],
            'account_type' => ['nullable', 'string', 'in:business,saler,customer'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $accountTypeStr = $validated['role'] ?? ($validated['account_type'] ?? UserRole::Business->value);
        $role = UserRole::tryFrom($accountTypeStr) ?? UserRole::Business;

        $storeName = $validated['business_name'] ?? ($validated['store_name'] ?? $validated['name']);
        $phone = !empty($validated['phone']) ? trim($validated['phone']) : null;

        try {
            $user = $this->registrationService->register([
                'account_type' => $role->value,
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'phone' => $phone,
                'password' => $validated['password'],
                'business_name' => $storeName,
                'display_name' => $storeName,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                if (str_contains($e->getMessage(), 'users_phone_unique')) {
                    throw ValidationException::withMessages([
                        'phone' => ['The phone number has already been taken.'],
                    ]);
                }
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    throw ValidationException::withMessages([
                        'email' => ['The email has already been taken.'],
                    ]);
                }
            }
            throw $e;
        }

        $token = $user->createToken('flutter-mobile-seller-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => new SellerUserResource($user),
        ], 201);
    }

    /**
     * Authenticate seller / customer account with email OR phone number (matching web LoginRequest).
     */
    public function login(Request $request): JsonResponse
    {
        $loginInput = $request->input('login') ?? ($request->input('email') ?? $request->input('phone'));
        if (! $loginInput) {
            throw ValidationException::withMessages([
                'email' => ['The email or phone number field is required.'],
            ]);
        }

        $password = $request->input('password');
        if (! $password) {
            throw ValidationException::withMessages([
                'password' => ['The password field is required.'],
            ]);
        }

        $field = str_contains((string) $loginInput, '@') ? 'email' : 'phone';

        if (! Auth::attempt([$field => $loginInput, 'password' => $password])) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials do not match our records.'],
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Enforce that only Sellers (saler), Store Owners (business), or Customers can access the seller app
        if (! $user->isSaler() && ! $user->isBusiness() && $user->role !== UserRole::Customer) {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. This app is strictly for Sellers, Customers and Store Owners.',
            ], 403);
        }

        if ($user->status === UserStatus::Suspended || $user->status === UserStatus::Blocked) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been restricted. Please contact support.',
            ], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('flutter-mobile-seller-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new SellerUserResource($user),
        ]);
    }

    /**
     * Request a 6-digit OTP to reset forgotten password.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We could not find an account with that email address.',
        ]);

        $otp = sprintf("%06d", mt_rand(100000, 999999));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            Mail::raw("Hello,\n\nYour OpenBox seller password reset OTP code is: {$otp}\n\nPlease enter this OTP in your mobile app to verify your password reset.\n\nThank you,\nOpenBox Team", function ($message) use ($request) {
                $message->to($request->email)->subject("OpenBox - Seller Password Reset OTP Code");
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to send seller password reset OTP email to {$request->email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Password reset OTP has been sent to your email address.',
            'email' => $request->email,
            'otp' => app()->environment('local') ? $otp : null,
        ]);
    }

    /**
     * Verify the 6-digit OTP for password reset.
     */
    public function verifyResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return response()->json([
                'message' => 'Invalid or expired OTP code. Please request a new one.',
            ], 422);
        }

        return response()->json([
            'message' => 'OTP verified successfully. Please enter your new password.',
            'token' => $request->otp,
        ]);
    }

    /**
     * Reset the user password using verified OTP token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (! $record || ! Hash::check($request->token, $record->token)) {
            return response()->json([
                'message' => 'Invalid or expired OTP code. Please request a new code.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password has been reset successfully. Please sign in.',
        ]);
    }

    /**
     * Verify email via OTP.
     */
    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return response()->json(['message' => 'Invalid or expired OTP code.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->status = UserStatus::Active;
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Email verified successfully!',
        ]);
    }

    /**
     * Resend email verification OTP.
     */
    public function resendEmailOtp(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $email = $user->email;

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            Mail::raw("Hello {$user->name},\n\nYour OpenBox email verification OTP code is: {$otp}\n\nThank you,\nOpenBox Team", function ($message) use ($email) {
                $message->to($email)->subject("OpenBox - Seller Email Verification OTP Code");
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to resend verification OTP to {$email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Verification OTP has been resent to your email.',
        ]);
    }

    /**
     * Send phone OTP.
     */
    public function sendPhoneOtp(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->phone) {
            return response()->json(['message' => 'Please add a phone number to your account first.'], 422);
        }

        $this->phoneVerification->send($user);

        return response()->json([
            'message' => 'Phone verification code sent successfully.',
        ]);
    }

    /**
     * Confirm phone OTP.
     */
    public function confirmPhoneOtp(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        /** @var User $user */
        $user = $request->user();

        if (! $this->phoneVerification->verify($user, $request->string('code')->value())) {
            return response()->json(['message' => 'That code is incorrect or has expired.'], 422);
        }

        return response()->json([
            'message' => 'Phone number verified successfully.',
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

    /**
     * Authenticate or register a seller/customer via Google or Facebook.
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:google,facebook'],
            'access_token' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
            'provider_id' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'in:business,saler,customer'],
            'account_type' => ['nullable', 'string', 'in:business,saler,customer'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
        ]);

        $provider = $validated['provider'];
        $idColumn = $provider === 'google' ? 'google_id' : 'facebook_id';

        $providerId = $validated['provider_id'] ?? null;
        $email = $validated['email'] ?? null;
        $name = $validated['name'] ?? null;
        $avatar = $validated['avatar'] ?? null;

        if (! empty($validated['access_token'])) {
            try {
                $socialUser = Socialite::driver($provider)->stateless()->userFromToken($validated['access_token']);
                $providerId = $socialUser->getId() ?: $providerId;
                $email = $socialUser->getEmail() ?: $email;
                $name = $socialUser->getName() ?: $name;
                $avatar = $socialUser->getAvatar() ?: $avatar;
            } catch (\Throwable $e) {
                if (! $email && ! $providerId) {
                    return response()->json([
                        'message' => 'Invalid or expired ' . ucfirst($provider) . ' access token.',
                    ], 422);
                }
            }
        }

        if (! $email && ! $providerId) {
            return response()->json([
                'message' => 'Unable to retrieve email or account identifier from ' . ucfirst($provider) . '.',
            ], 422);
        }

        $email = $email ?: ($provider . '_' . $providerId . '@social.openbox.com');
        $name = $name ?: ucfirst($provider) . ' User';

        $user = User::query()
            ->when($providerId, fn ($q) => $q->where($idColumn, $providerId))
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $updates = [];
            if ($providerId && ! $user->{$idColumn}) {
                $updates[$idColumn] = $providerId;
            }
            if (! $user->avatar && $avatar) {
                $updates['avatar'] = $avatar;
            }
            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }
            if (! empty($updates)) {
                $user->update($updates);
            }
        } else {
            $accountTypeStr = $validated['role'] ?? ($validated['account_type'] ?? UserRole::Business->value);
            $role = UserRole::tryFrom($accountTypeStr) ?? UserRole::Business;
            $storeName = $validated['store_name'] ?? ($validated['business_name'] ?? $name);

            $user = $this->registrationService->register([
                'account_type' => $role->value,
                'name' => $name,
                'email' => $email,
                'password' => Str::random(32),
                'business_name' => $storeName,
                'display_name' => $storeName,
            ]);

            $user->update([
                $idColumn => $providerId,
                'avatar' => $avatar,
                'email_verified_at' => now(),
            ]);
        }

        if ($user->status === UserStatus::Suspended || $user->status === UserStatus::Blocked) {
            return response()->json([
                'message' => 'Your account has been restricted. Please contact support.',
            ], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('flutter-mobile-seller-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new SellerUserResource($user),
        ]);
    }
}