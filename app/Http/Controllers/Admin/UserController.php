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
        $roleValue = match ($roleInput) {
            'seller', 'saler' => UserRole::Saler->value,
            'store owner', 'store_owner', 'store-owner', 'business' => UserRole::Business->value,
            'user', 'customer' => UserRole::Customer->value,
            'verifier' => UserRole::Verifier->value,
            'admin' => UserRole::Admin->value,
            default => UserRole::tryFrom($roleInput)?->value,
        };

        $statusInput = strtolower(trim((string) $request->input('status')));
        $statusValue = UserStatus::tryFrom($statusInput)?->value;

        $search = trim((string) $request->input('search'));

        $users = User::query()
            ->when($roleValue, fn ($query) => $query->where('role', $roleValue))
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

        $totalUsersCount = User::count();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
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
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg,gif', 'max:5120'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarPath = \App\Helpers\FileUploadHelper::store($request->file('avatar'), 'avatars', 'public');
            $validated['avatar'] = $avatarPath;
            $user->profile()->updateOrCreate(['user_id' => $user->id], ['avatar' => $avatarPath]);
        }

        $this->users->updateUser($user, $validated);

        return back()->with('status', "{$user->name}'s account was updated successfully.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own active administrator account.');
        }

        $userName = $user->name;
        $this->users->deleteUser($user);

        return back()->with('status', "User {$userName} was deleted successfully.");
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
