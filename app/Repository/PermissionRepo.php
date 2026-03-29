<?php 
namespace App\Repository;

use App\Models\User;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class PermissionRepo{
    public function createrole($data){
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'api',
        ]);
        if(!$role){
            throw new Exception('Failed to create role');
        }
        if (!empty($data['permissions'])) {
            $role->givePermissionTo($data['permissions']);
        }
        $role->load('permissions');
        return $role;
    }
    public function deleterole($data){
        $role = Role::where('id', $data['id'])->firstOrFail();
        if($role->name == 'Super Admin'){
            throw new Exception('You cannot delete this role');
        }
        $role->delete();
        return $role;
    }
    public function assignrole($data , $user){
        $targetUser = User::findOrFail($data['user_id']);
        $roles = $data['role'];
        if(str_contains($roles,'Super Admin')){
            if(!$user->hasRole('Super Admin')){
                throw new Exception('You are not allowed to perform this action');
            }
        }
        if (empty($roles)) {
            $roles = ['Customer'];
        }

        $targetUser->syncRoles($roles);
        $targetUser->refresh();
        $targetUser->load('roles');
        return $targetUser;
    }
    public function removerole($data, $user){
        $targetUser = User::findOrFail($data['user_id']);
        if(!$targetUser->hasRole($data['role'])){
            throw new Exception('User does not have this role');
        }
        if ($targetUser->hasRole('Super Admin')) {
            throw new Exception('You cannot remove this role');
        }
        $targetUser->removeRole($data['role']);
        if ($targetUser->roles()->count() == 0) {
            $targetUser->assignRole('Customer');
        }
        $targetUser->refresh();
        $targetUser->load('roles');
        return $targetUser;
    }
    public function getallroles(){
        $roles = Role::with('permissions')->get();
        return $roles;
    }
    public function createpermission($data){
        $permission = Permission::firstOrNew([
            'name' => $data['name'],
            'guard_name' => 'api',
        ]);
        if($permission->exists){
            throw new Exception('Permission already exists');
        }
        $permission->save(); 
        return $permission;
    }
    public function deletepermission($data){
        $permission = Permission::where('name', $data['name'])->where('guard_name', 'api')->firstOrFail();
        $permission->delete();
        return $permission;
    }
    public function getallpermissions(){
        $permissions = Permission::all();
        return $permissions;
    }
    public function assignpermission($data, $user){
        $role = Role::findOrFail($data['role_id']);
        // Super Admin role permissions cannot be modified
        if ($role->name === 'Super Admin') {
            throw new Exception('You cannot modify Super Admin role permissions');
        }
        $role->syncPermissions($data['permission']); // sync permissions to role bcz it will remove all previous permissions from role and add new permissions
        $role->refresh();
        $data = [
            'role' => $role,
            'permissions' => $role->permissions->pluck('name'),
        ];
        return $data;
    }
    public function removepermissionfromrole($data){
        $role = Role::findOrFail($data['role_id']);
        // Super Admin role permissions cannot be modified
        if ($role->name === 'Super Admin') {
            throw new Exception('You cannot modify Super Admin role permissions');
        }
        $role_per = $role->permissions->pluck('name')->toArray(); // instead of loop we can use pluck to get all permissions name in array and then diff it with request permission
        $check = array_diff($data['permission'], $role_per); // array_diff is used to get the difference between two arrays 
        if(!empty($check)){ // if not in role is not empty then it means that role does not have this permission
            throw new Exception('Permission not found for this role');
        }
        $role->revokePermissionTo($data['permission']); // revoke permissions from role bcz it will remove all previous permissions from role and add new permissions
        $role->refresh();
        $data = [
            'role' => $role,
            'permissions' => $role->permissions->pluck('name'),
        ];
        return $data;
    }
    public function getuserrolepermission($data , $user){
        $targetuser = $data['user_id'] ?? $user->id;
        $targetuser = User::findOrFail($targetuser);
        $data = [
            'user' => $targetuser,
            'roles' => $targetuser->roles->pluck('name'),
            'permissions' => $targetuser->getAllPermissions()->pluck('name')->unique(),
        ];
        return $data;
    }
}