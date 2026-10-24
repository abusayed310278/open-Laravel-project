<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserManagementService $users) {}

    public function index(Request $request): View
    {
        $roleInput = strtolower(trim((string) $request->input('role')));
        $roleValues = match ($roleInput) {
            'seller', 'saler', 'user', 'customer', 'personal' => [UserRole::Customer->value, UserRole::Saler->value],
            'store owner', 'store_owner', 'store-owner', 'business' => [UserRole::Business->value],
            'verifier' => [UserRole::Verifier->value],
            'admin' => [UserRole::Admin->value],
            default => $roleInput !== '' && UserRole::tryFrom($roleInput) ? [UserRole::tryFrom($roleInput)->value] : null,
        };

        $statusInput = strtolower(trim((string) $request->input('status')));
        $statusValue = UserStatus::tryFrom($statusInput)?->value;

        $search = trim((string) $request->input('search'));

        $users = User::query()
            ->withCount(['orders', 'products'])
            ->when($roleValues, fn ($query) => $query->whereIn('role', $roleValues))
            ->when($statusValue, fn ($query) => $query->where('status', $statusValue))
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                );
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roleCounts = [];
        $rawCounts = User::query()
            ->toBase()
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->all();

        foreach ($rawCounts as $r => $cnt) {
            $key = $r instanceof UserRole ? $r->value : (string) $r;
            $roleCounts[$key] = (int) $cnt;
        }

        // Combine legacy saler and customer counts under customer for clean display
        $roleCounts[UserRole::Customer->value] = ($roleCounts[UserRole::Customer->value] ?? 0) + ($roleCounts[UserRole::Saler->value] ?? 0);

        $totalUsersCount = User::count();

        // Omit legacy Saler from visible tabs so admin sees: Personal (Buy & Sell), Store Owner, Verifier, Admin
        $visibleRoles = collect(UserRole::cases())
            ->reject(fn (UserRole $role) => $role === UserRole::Saler)
            ->values()
            ->all();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $visibleRoles,
            'allRoles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
            'roleCounts' => $roleCounts,
            'totalUsersCount' => $totalUsersCount,
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'store_name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg,gif', 'max:5120'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $storeName = $validated['store_name'] ?? null;
        unset($validated['store_name']);

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarPath = \App\Helpers\FileUploadHelper::store($request->file('avatar'), 'avatars', 'public');
            $validated['avatar'] = $avatarPath;
            $user->profile()->updateOrCreate(['user_id' => $user->id], ['avatar' => $avatarPath]);
        }

        $this->users->updateUser($user, $validated);

        if ($storeName !== null && $storeName !== '') {
            if ($user->salerProfile) {
                $user->salerProfile->update(['display_name' => $storeName]);
            }
            if ($user->businessProfile) {
                $user->businessProfile->update(['business_name' => $storeName]);
            }
            if ($user->profile) {
                $user->profile->update(['company' => $storeName]);
            }
        }

        return back()->with('status', "{$user->name}'s account was updated successfully.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own active administrator account.');
        }

        $userName = $user->name;

        try {
            $this->users->deleteUser($user);

            return back()->with('status', "User {$userName} was deleted successfully.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete user {$user->id}: " . $e->getMessage(), ['exception' => $e]);

            return back()->with('error', "Could not delete user {$userName}: " . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = array_diff($validated['ids'], [$request->user()->id]);

        if (empty($ids)) {
            return back()->with('error', 'You cannot delete your own active administrator account.');
        }

        try {
            $count = $this->users->bulkDeleteUsers($ids);

            return back()->with('status', "{$count} user(s) deleted successfully.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Bulk delete failed: ' . $e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Could not complete bulk deletion: ' . $e->getMessage());
        }
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $this->users->updateStatus($user, $request->enum('status', UserStatus::class));

        return back()->with('status', "{$user->name}'s account status was updated.");
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->users->updateRole($user, $request->enum('role', UserRole::class));

        return back()->with('status', "{$user->name}'s role was updated.");
    }
}
