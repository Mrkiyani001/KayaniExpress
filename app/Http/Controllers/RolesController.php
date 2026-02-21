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
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
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
            return $this->Response(true, 'Role created successfully', $role, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to create role'.$e->getMessage(), [], 400);
        }
    }

    public function deleteRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'id' => 'required|exists:roles,id',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }

            $role = Role::find($request->id);
            if (!$role) {
                return $this->Response(false, 'Role not found', [], 404);
            }

            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }

            $role->delete();
            return $this->Response(true, 'Role deleted successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to delete role'.$e->getMessage(), [], 400);
        }
    }

    public function assignRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'role' => 'present|string',
            'user_id' => 'required|exists:users,id',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }

            $targetUser = User::findOrFail($request->user_id);
            $roles = $request->role;
            if(str_contains($roles,'Super Admin')){
                if(!$user->hasRole('Super Admin')){
                    return $this->NotAllowed();
                }
            }
            if (empty($roles)) {
                $roles = ['Customer'];
            }

            $targetUser->syncRoles($roles);
            $targetUser->refresh();
            $targetUser->load('roles');
            return $this->Response(true, 'Role assigned successfully', $targetUser, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to assign role'.$e->getMessage(), [], 400);
        }
    }

    public function unassignRole(Request $request)
    {
        $this->ValidateRequest($request, [
            'role' => 'present|string',
            'user_id' => 'required|exists:users,id',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
              $targetUser = User::findOrFail($request->user_id);

            if ($targetUser->hasRole('Super Admin')) {
                return $this->NotAllowed();
            }
            if(!$targetUser->hasRole($request->role)){
                return $this->Response(false, 'User does not have this role', [], 400);
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $targetUser->removeRole($request->role);

            // Ensure user has at least one role, default to 'user' if none left
            if ($targetUser->roles()->count() == 0) {
                $targetUser->assignRole('Customer');
            }
            $targetUser->refresh();
            $targetUser->load('role');

            return $this->Response(true, 'Role unassigned successfully', $targetUser, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to unassign role'.$e->getMessage(), [], 400);
        }
    }
    public function getAllRoles()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }

            $roles = Role::all();
            $roles->load('permissions');
            return $this->Response(true, 'Roles fetched successfully', $roles, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch roles'.$e->getMessage(), [], 400);
        }
    }
    public function createPermission(Request $request){
        $this->ValidateRequest($request, [
            'name'=>'required|string|max:255',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            return $this->Response(true, 'Permission created successfully', $permission, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to create permission'.$e->getMessage(), [], 400);
        }
    }
    public function deletePermission(Request $request){
        $this->ValidateRequest($request, [
            'name'=>'required|exists:permissions,name',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $permission = Permission::where('name', $request->name)->where('guard_name', 'api')->firstOrFail();
            $permission->delete();
            return $this->Response(true, 'Permission deleted successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to delete permission'.$e->getMessage(), [], 400);
        }
    }
    public function getAllPermissions(){
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $permissions = Permission::all();
            return $this->Response(true, 'Permissions fetched successfully', $permissions, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch permissions'.$e->getMessage(), [], 400);
        }
    }
    public function assignPermissionToRole(Request $request){
        $this->ValidateRequest($request, [
            'role_id'=>'required|exists:roles,id',
            'permission'=>'required|array',
            'permission.*'=>'string|exists:permissions,name',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($request->role_id);

            // Super Admin role permissions cannot be modified
            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }

            $role->syncPermissions($request->permission);
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Permission assigned successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to assign permission'.$e->getMessage(), [], 400);
        }
    }
    public function removePermissionFromRole(Request $request){
        $this->ValidateRequest($request, [
            'role_id'=>'required|exists:roles,id',
            'permission'=>'required|array',
            'permission.*'=>'string|exists:permissions,name',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($request->role_id);

            // Super Admin role permissions cannot be removed
            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }

            $role->revokePermissionTo($request->permission);
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Permission removed successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to remove permission'.$e->getMessage(), [], 400);
        }
    }
    public function updatePermissionForRole(Request $request){
        $this->ValidateRequest($request, [
            'role_id'=>'required|exists:roles,id',
            'permission'=>'required|array',
            'permission.*'=>'string|exists:permissions,name',
        ]);
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($request->role_id);

            // Super Admin role permissions cannot be updated
            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }

            $role->syncPermissions($request->permission);
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Permission updated successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to update permission'.$e->getMessage(), [], 400);
        }
    }
    public function getUserRolePermission(Request $request){
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $user = User::findOrFail($request->user_id);
            $data = [
                'user' => $user,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')->unique(),
            ];
            return $this->Response(true, 'User role and permission fetched successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch user role and permission: ' . $e->getMessage(), [], 400);
        }
    }
}