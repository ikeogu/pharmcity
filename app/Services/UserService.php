<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

class UserService
{

    public function fetchUsers(Request $request)
    {

        return User::with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm)
                        ->orWhere('username', 'like', $searchTerm);
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();
    }

    public function storeStaff(array $data)
    {

        $user = User::create(Arr::except($data, 'role_id'));

        $role = Role::find($data['role_id']);

        $user->assignRole($role->name);

        return $user;
    }

    public function updateStaff(array $data, User $user)
    {

        // 1. Update user attributes except the role
        $user->update(Arr::except($data, ['role_id']));

        // 2. Handle role assignment (if provided)
        if (!empty($data['role_id'])) {
            $role = Role::find($data['role_id']);
            if ($role) {
                $user->syncRoles([$role->name]); // replaces existing roles
            }
        }

        // 3. Reload the user with fresh relationships
        $user->load('roles');

        // 4. Return updated instance
        return $user;
    }
}
