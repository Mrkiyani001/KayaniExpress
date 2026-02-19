<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends BaseController
{
    public function createRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'name' => 'required|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'sometimes|exists:permissions,name',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            if ($request->has('permissions')) {
                $role->givePermissionTo($request->permissions);
            }

            $role->load('permissions');
            return $this->Response(true, 'Success', 'Role created successfully', $role, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to create role', [], 400);
        }
    }

    public function deleteRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'id' => 'required|exists:roles,id',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }

            $role = Role::find($request->id);
            if (!$role) {
                return $this->Response(false, 'Role not found', [], 404);
            }

            if ($role->name === 'admin') {
                return $this->NotAllowed();
            }

            $role->delete();
            return $this->Response(true, 'Success', 'Role deleted successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to delete role', [], 400);
        }
    }

    public function assignRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'role' => 'present|array',
            'user_id' => 'required|exists:users,id',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }

            $targetUser = User::findOrFail($request->user_id);
            $roles = $request->role;

            if (empty($roles)) {
                $roles = ['user'];
            }

            $targetUser->assignRole($roles);
            return $this->Response(true, 'Success', 'Role assigned successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to assign role', [], 400);
        }
    }

    public function unassignRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'role' => 'present|array',
            'user_id' => 'required|exists:users,id',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }

            $targetUser = User::findOrFail($request->user_id);

            if ($targetUser->hasRole('admin')) {
                return $this->NotAllowed();
            }

            $targetUser->removeRole($request->role);

            // Ensure user has at least one role, default to 'user' if none left
            if ($targetUser->roles()->count() == 0) {
                $targetUser->assignRole('user');
            }

            return $this->Response(true, 'Success', 'Role unassigned successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to unassign role', [], 400);
        }
    }
}