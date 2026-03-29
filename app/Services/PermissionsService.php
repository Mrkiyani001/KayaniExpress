<?php 
namespace App\Services;

use App\Repository\PermissionRepo;
use Exception;

class PermissionsService{
    private $permissionRepo;
    public function __construct(PermissionRepo $permissionRepo){
        $this->permissionRepo = $permissionRepo;
    }
    public function createrole($data){
        try{
            $role = $this->permissionRepo->createrole($data);
            return $role;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function deleterole($data){
        try{
            $role = $this->permissionRepo->deleterole($data);
            return $role;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function assignrole($data, $user){
        try{
            $role = $this->permissionRepo->assignrole($data, $user);
            return $role;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function removerole($data, $user){
        try{
            $role = $this->permissionRepo->removerole($data, $user);
            return $role;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function getallroles(){
        try{
            $roles = $this->permissionRepo->getallroles();
            return $roles;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function createpermission($data){
        try{
            $permission = $this->permissionRepo->createpermission($data);
            return $permission;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function deletepermission($data){
        try{
            $permission = $this->permissionRepo->deletepermission($data);
            return $permission;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function getallpermissions(){
        try{
            $permissions = $this->permissionRepo->getallpermissions();
            return $permissions;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function assignpermission($data, $user){
        try{
            $permission = $this->permissionRepo->assignpermission($data, $user);
            return $permission;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function removepermissionfromrole($data){
        try{
            $permission = $this->permissionRepo->removepermissionfromrole($data);
            return $permission;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function getuserrolepermission($data , $user){
        try{
            $permission = $this->permissionRepo->getuserrolepermission($data , $user);
            return $permission;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}