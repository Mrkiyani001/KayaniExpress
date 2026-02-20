<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
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
    public function getAllRoles()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }

            $roles = Role::all();
            $roles->load('permissions');
            return $this->Response(true, 'Success', 'Roles fetched successfully', $roles, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch roles', [], 400);
        }
    }
    public function createPermission(Request $request){
        $this->ValidateRequest($request, [
            'name'=>'required|string|max:255',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            return $this->Response(true, 'Success', 'Permission created successfully', $permission, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to create permission', [], 400);
        }
    }
    public function deletePermission(Request $request){
        $this->ValidateRequest($request, [
            'name'=>'required|exists:permissions,name',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $permission = Permission::where('name', $request->name)->where('guard_name', 'api')->firstOrFail();
            $permission->delete();
            return $this->Response(true, 'Success', 'Permission deleted successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to delete permission', [], 400);
        }
    }
    public function getAllPermissions(){
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $permissions = Permission::all();
            return $this->Response(true, 'Success', 'Permissions fetched successfully', $permissions, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch permissions', [], 400);
        }
    }
    public function assignPermissionToRole(Request $request){
        $this->ValidateRequest($request, [
            'role_id'=>'required|exists:roles,id',
            'permission'=>'required|array',
            'permission.*'=>'string|exists:permissions,name',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($request->role_id);
            $role->syncPermissions($request->permission);
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Success', 'Permission assigned successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to assign permission', [], 400);
        }
    }
    public function removePermissionFromRole(Request $request){
        $this->ValidateRequest($request, [
            'role_id'=>'required|exists:roles,id',
            'permission'=>'required|array',
            'permission.*'=>'string|exists:permissions,name',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($request->role_id);
            $role->revokePermissionTo($request->permission);
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Success', 'Permission removed successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to remove permission', [], 400);
        }
    }
    public function updatePermissionForRole(Request $request){
        $this->ValidateRequest($request, [
            'role_id'=>'required|exists:roles,id',
            'permission'=>'required|array',
            'permission.*'=>'string|exists:permissions,name',
        ]);
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($request->role_id);
            $role->syncPermissions($request->permission);
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Success', 'Permission updated successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to update permission', [], 400);
        }
    }
    public function getUserRolePermission(Request $request){
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole('admin')) {
                return $this->NotAllowed();
            }
            $user = User::findOrFail($request->user_id);
            $data = [
                'user' => $user,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')->unique(),
            ];
            return $this->Response(true, 'Success', 'User role and permission fetched successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch user role and permission: ' . $e->getMessage(), [], 400);
        }
    }
}