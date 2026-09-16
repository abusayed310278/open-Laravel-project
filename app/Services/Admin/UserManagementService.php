<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class UserManagementService
{
    public function updateStatus(User $user, UserStatus $status): User
    {
        $user->update(['status' => $status]);

        return $user;
    }

    public function updateRole(User $user, UserRole $role): User
    {
        $user->update(['role' => $role]);

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return (bool) $user->delete();
    }
}
