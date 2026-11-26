<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBlockController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        /** @var User $auth */
        $auth = $request->user() ?: auth('sanctum')->user();

        $blockedUsers = $auth->blockedUsers()
            ->with(['profile', 'businessProfile', 'salerProfile'])
            ->latest('user_blocks.created_at')
            ->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => $blockedUsers->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->businessProfile?->business_name ?? $user->salerProfile?->display_name ?? $user->name,
                    'email' => $user->email,
                    'avatar' => $user->profile?->avatarUrl() ?? ($user->avatar ? \App\Support\MediaUrl::resolve($user->avatar) : null),
                    'blocked_at' => $user->pivot?->created_at?->toISOString(),
                ]),
            ]);
        }

        return view('account.blocked-users.index', [
            'blockedUsers' => $blockedUsers,
        ]);
    }

    public function store(Request $request, User $user): JsonResponse|RedirectResponse
    {
        /** @var User $auth */
        $auth = $request->user() ?: auth('sanctum')->user();

        if ($auth->id === $user->id) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'You cannot block yourself.'], 422);
            }

            return back()->with('error', 'You cannot block yourself.');
        }

        $auth->block($user, $request->string('reason')->value() ?: null);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'User blocked successfully.',
                'blocked_user_id' => $user->id,
            ]);
        }

        return back()->with('status', 'Seller has been blocked. Their products are now hidden from your search results.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        /** @var User $auth */
        $auth = $request->user() ?: auth('sanctum')->user();

        $auth->unblock($user);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'User unblocked successfully.',
                'unblocked_user_id' => $user->id,
            ]);
        }

        return back()->with('status', 'User unblocked successfully.');
    }
}
