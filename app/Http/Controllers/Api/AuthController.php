<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Helpers\FileUploadHelper;
use App\Support\MediaUrl;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Mobile app is restricted to Customer / User role only
        $roleValue = is_object($user->role) ? $user->role->value : $user->role;
        if ($roleValue !== UserRole::Customer->value && $roleValue !== 'customer' && $roleValue !== 'user') {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. This app is for Customer accounts only.',
            ], 403);
        }

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otp = sprintf("%06d", mt_rand(100000, 999999));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Customer,
            'status' => UserStatus::Pending,
        ]);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            Mail::raw("Hello {$user->name},\n\nYour OpenBox email verification OTP code is: {$otp}\n\nPlease enter this OTP in your mobile app to verify your email.\n\nThank you,\nOpenBox Team", function ($message) use ($user) {
                $message->to($user->email)->subject("OpenBox - Email Verification OTP Code");
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to send registration OTP email to {$user->email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registration successful. An OTP code has been sent to your email address.',
            'email' => $user->email,
        ], 201);
    }

    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return response()->json(['message' => 'Invalid or expired OTP code'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->status = UserStatus::Active;
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Email verified successfully! Please sign in to continue.',
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

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
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
            Mail::raw("Hello,\n\nYour OpenBox password reset OTP code is: {$otp}\n\nPlease enter this code in your mobile app to verify your password reset.\n\nThank you,\nOpenBox Team", function ($message) use ($request) {
                $message->to($request->email)->subject("OpenBox - Password Reset OTP Code");
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to send password reset OTP email to {$request->email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Password reset OTP has been sent to your email address.',
            'email' => $request->email,
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
            return response()->json(['message' => 'Invalid or expired OTP code'], 422);
        }

        return response()->json([
            'message' => 'OTP verified successfully. Please enter your new password.',
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
            return response()->json(['message' => 'Invalid or expired OTP code'], 422);
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
