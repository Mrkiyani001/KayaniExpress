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
use App\Policies\RolePolicy;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\PermissionsService;
use Illuminate\Support\Facades\DB;

class RolesController extends BaseController
{
    private $permissionService;
    public function __construct(PermissionsService $permissionService){
        $this->permissionService = $permissionService;
    }
    public function createRole(CreateRoleRequest $request)
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $role = $this->permissionService->createrole($data);
            DB::commit();
            return $this->Response(true, 'Role created successfully', $role, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, 'Failed to create role'.$e->getMessage(), [], 500);
        }
    }

    public function deleteRole(DeleteRoleRequest $request)
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $role = $this->permissionService->deleterole($data);
            DB::commit();
            return $this->Response(true, 'Role deleted successfully', $role, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, 'Failed to delete role'.$e->getMessage(), [], 500);
        }
    }

    public function assignRole(AssignRoleRequest $request)
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $targetUser = $this->permissionService->assignrole($data, $user);
            DB::commit();
            return $this->Response(true, 'Role assigned successfully', $targetUser, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, 'Failed to assign role'.$e->getMessage(), [], 500);
        }
    }

    public function unassignRole(UnAssignRoleRequest $request)
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $targetUser = $this->permissionService->removerole($data, $user);
            DB::commit();
            return $this->Response(true, 'Role unassigned successfully', $targetUser, 200);
        } catch (Exception $e) {
            DB::rollBack();
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
            $this->authorize('checkrole', Role::class);
            $roles = $this->permissionService->getallroles();
            return $this->Response(true, 'Roles fetched successfully', $roles, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch roles'.$e->getMessage(), [], 500);
        }
    }
    public function createPermission(CreatePerRequest $request){
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
           $permission = $this->permissionService->createpermission($data);
           DB::commit();
            return $this->Response(true, 'Permission created successfully', $permission, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, 'Failed to create permission' .$e->getMessage(), [], 500);
        }
    }
    public function deletePermission(DeletePerRequest $request){
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $permission = $this->permissionService->deletepermission($data);
            DB::commit();
            return $this->Response(true, 'Permission deleted successfully', [], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, 'Failed to delete permission'.$e->getMessage(), [], 500);
        }
    }
    public function getAllPermissions(){
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $permissions = $this->permissionService->getallpermissions();
            return $this->Response(true, 'Permissions fetched successfully', $permissions, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch permissions'.$e->getMessage(), [], 500);
        }
    }
    public function assignPermissionToRole(AssignPerToRoleRequest $request){
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $permission = $this->permissionService->assignpermission($data, $user);
            DB::commit();
            return $this->Response(true, 'Permission assigned successfully', $permission, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->Response(false, 'Failed to assign permission'.$e->getMessage(), [], 500);
        }
    }
    public function removePermissionFromRole(UnAssignPerFromRoleRequest $request){
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $user = auth('api')->user();
            if (!$user) {
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
           $permission = $this->permissionService->removepermissionfromrole($data);
           DB::commit();
            return $this->Response(true, 'Permission removed successfully', $permission, 200);
        } catch (Exception $e) {
            DB::rollBack();
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
            $this->authorize('checkrole', Role::class);
            $targetUser = $this->permissionService->getuserrolepermission($data , $user);
            return $this->Response(true, 'User role and permission fetched successfully', $targetUser, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Failed to fetch user role and permission: ' . $e->getMessage(), [], 500);
        }
    }
}