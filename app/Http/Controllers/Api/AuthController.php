<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\PhoneVerificationService;
use App\Services\Auth\RegistrationService;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly PhoneVerificationService $phoneVerification,
    ) {}

    /**
     * Authenticate user with either email or phone number (matching web.php LoginRequest).
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

        // Allow Customer, Saler, and Business roles (seller/customer/user are same)
        $roleValue = is_object($user->role) ? $user->role->value : $user->role;
        $allowedRoles = [
            UserRole::Customer->value, 'customer', 'user',
            UserRole::Saler->value, 'saler',
            UserRole::Business->value, 'business',
        ];

        if (! in_array($roleValue, $allowedRoles, strict: true)) {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. Account role not authorized for mobile application.',
            ], 403);
        }

        if ($user->status === UserStatus::Suspended || $user->status === UserStatus::Blocked) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been restricted. Please contact support.',
            ], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Register a new user/customer/seller account using RegistrationService.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'account_type' => ['nullable', 'string', 'in:customer,business,saler'],
            'role' => ['nullable', 'string', 'in:customer,business,saler'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $accountTypeStr = $validated['role'] ?? ($validated['account_type'] ?? UserRole::Customer->value);
        $role = UserRole::tryFrom($accountTypeStr) ?? UserRole::Customer;

        $storeName = $validated['business_name'] ?? ($validated['store_name'] ?? ($validated['display_name'] ?? $validated['name']));
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

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Social Authentication via Google or Facebook.
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:google,facebook'],
            'access_token' => ['nullable', 'string'],
            'provider_id' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'in:customer,business,saler'],
            'account_type' => ['nullable', 'string', 'in:customer,business,saler'],
            'store_name' => ['nullable', 'string', 'max:255'],
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
                'message' => 'Unable to retrieve email or identifier from ' . ucfirst($provider) . '.',
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
            $accountTypeStr = $validated['role'] ?? ($validated['account_type'] ?? UserRole::Customer->value);
            $role = UserRole::tryFrom($accountTypeStr) ?? UserRole::Customer;
            $storeName = $validated['store_name'] ?? $name;

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
        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

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
            Mail::raw("Hello,\n\nYour OpenBox password reset OTP code is: {$otp}\n\nPlease enter this OTP in your mobile app to verify your password reset.\n\nThank you,\nOpenBox Team", function ($message) use ($request) {
                $message->to($request->email)->subject("OpenBox - Password Reset OTP Code");
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to send password reset OTP email to {$request->email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Password reset OTP has been sent to your email address.',
            'email' => $request->email,
            'otp' => app()->environment('local') ? $otp : null,
        ]);
    }

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
                'message' => 'Invalid or expired OTP code. Please request a new one.',
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
                $message->to($email)->subject("OpenBox - Email Verification OTP Code");
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to resend verification OTP to {$email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Verification OTP has been resent to your email.',
        ]);
    }

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

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg,gif', 'max:5120'],
            'remove_avatar' => ['nullable'],
        ]);

        $user->name = $data['name'];
        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }
        $user->save();

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            if ($user->profile?->avatar) {
                MediaUrl::delete($user->profile->avatar);
            }
            $avatarPath = FileUploadHelper::store($request->file('avatar'), 'avatars');
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['avatar' => $avatarPath]
            );
        } elseif ($request->boolean('remove_avatar') || $request->input('remove_avatar') === '1' || $request->input('remove_avatar') === 'true') {
            if ($user->profile?->avatar) {
                MediaUrl::delete($user->profile->avatar);
                $user->profile()->update(['avatar' => null]);
            }
        }

        $user->load('profile');

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }
}