<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles_Permission\AssignPerToRoleRequest;
use App\Http\Requests\Roles_Permission\AssignRoleRequest;
use App\Http\Requests\Roles_Permission\CreatePerRequest;
use App\Http\Requests\Roles_Permission\CreateRoleRequest;
use App\Http\Requests\Roles_Permission\DeletePerRequest;
use App\Http\Requests\Roles_Permission\DeleteRoleRequest;
use App\Http\Requests\Roles_Permission\UnAssignPerFromRoleRequest;
use App\Http\Requests\Roles_Permission\UnAssignRoleRequest;
use App\Http\Requests\Roles_Permission\UserRolePerRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends BaseController
{
    public function createRole(CreateRoleRequest $request)
    {
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }

            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'api',
            ]);

            if (!empty($data['permissions'])) {
                $role->givePermissionTo($data['permissions']);
            }

            $role->load('permissions');
            return $this->Response(true, 'Role created successfully', $role, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to create role'.$e->getMessage(), [], 500);
        }
    }

    public function deleteRole(DeleteRoleRequest $request)
    {
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }

            $role = Role::find($data['id']);
            if (!$role) {
                return $this->Response(false, 'Role not found', [], 404);
            }

            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }

            $role->delete();
            return $this->Response(true, 'Role deleted successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to delete role'.$e->getMessage(), [], 500);
        }
    }

    public function assignRole(AssignRoleRequest $request)
    {
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }

            $targetUser = User::findOrFail($data['user_id']);
            $roles = $data['role'];
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
            return $this->Response(false, 'Failed to assign role'.$e->getMessage(), [], 500);
        }
    }

    public function unassignRole(UnAssignRoleRequest $request)
    {
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
              $targetUser = User::findOrFail($data['user_id']);

            if(!$targetUser->hasRole($data['role'])){
                return $this->Response(false, 'User does not have this role', [], 400);
            }
            if ($targetUser->hasRole('Super Admin')) {
                return $this->NotAllowed();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $targetUser->removeRole($data['role']);
            if ($targetUser->roles()->count() == 0) {
                $targetUser->assignRole('Customer');
            }
            $targetUser->refresh();
            $targetUser->load('role');

            return $this->Response(true, 'Role unassigned successfully', $targetUser, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to unassign role'.$e->getMessage(), [], 500);
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
            return $this->Response(false, 'Failed to fetch roles'.$e->getMessage(), [], 500);
        }
    }
    public function createPermission(CreatePerRequest $request){
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $permission = Permission::where('name', $data['name'])->where('guard_name', 'api')->first();
            if ($permission) {
                return $this->Response(false, 'Permission already exists', [], 400);
            }
            $permission = Permission::create([
                'name' => $data['name'],
                'guard_name' => 'api',
            ]);

            return $this->Response(true, 'Permission created successfully', $permission, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to create permission' .$e->getMessage(), [], 500);
        }
    }
    public function deletePermission(DeletePerRequest $request){
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $permission = Permission::where('name', $data['name'])->where('guard_name', 'api')->first();
            if (!$permission) {
                return $this->Response(false, 'Permission not found', [], 400);
            }
            $permission->delete();
            return $this->Response(true, 'Permission deleted successfully', [], 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to delete permission'.$e->getMessage(), [], 500);
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
            return $this->Response(false, 'Failed to fetch permissions'.$e->getMessage(), [], 500);
        }
    }
    public function assignPermissionToRole(AssignPerToRoleRequest $request){
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($data['role_id']);

            // Super Admin role permissions cannot be modified
            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }

            $role->syncPermissions($data['permission']); // sync permissions to role bcz it will remove all previous permissions from role and add new permissions
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Permission assigned successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to assign permission'.$e->getMessage(), [], 500);
        }
    }
    public function removePermissionFromRole(UnAssignPerFromRoleRequest $request){
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $role = Role::findOrFail($data['role_id']);

            // Super Admin role permissions cannot be removed
            if ($role->name === 'Super Admin') {
                return $this->NotAllowed();
            }
            $role_per = $role->permissions->pluck('name')->toArray();  // instead of loop we can use pluck to get all permissions and then diff it with request permission
            $Not_in_Role = array_diff($data['permission'], $role_per); // array_diff is used to get the difference between two arrays
            if (!empty($Not_in_Role)) {             // if not in role is not empty then return error
                return $this->Response(false, 'Role does not have permissions: ' . implode(', ', $Not_in_Role),[], 400); //implode is used to convert array to string
            }
            $role->revokePermissionTo($data['permission']); // revoke permission from role
            $role->refresh();
            $data = [
                'role' => $role,
                'permissions' => $role->permissions->pluck('name'),
            ];
            return $this->Response(true, 'Permission removed successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to remove permission'.$e->getMessage(), [], 500);
        }
    }
    public function getUserRolePermission(UserRolePerRequest $request){
        $data = $request->validated();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            if (!$user->hasRole(['Super Admin','Admin'])) {
                return $this->NotAllowed();
            }
            $targetUser = isset($data['user_id']) ? User::findOrFail($data['user_id']) : $user; // first it check if user_id is set or not if set then find user by user_id else use the authenticated user
            $data = [
                'user' => $targetUser,
                'roles' => $targetUser->getRoleNames(),
                'permissions' => $targetUser->getAllPermissions()->pluck('name')->unique(),
            ];
            return $this->Response(true, 'User role and permission fetched successfully', $data, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch user role and permission: ' . $e->getMessage(), [], 500);
        }
    }
}