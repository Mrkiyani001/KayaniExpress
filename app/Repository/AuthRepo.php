<?php
namespace App\Repository;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthRepo{
    public function create_user($data){
        $otp = rand(100000, 999999);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role_id' => Role::where('name', 'Customer')->first()->id,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);
        return $user;
    }
    public function find_user($data){ 
         $check = [];
            if(!empty($data['email'])){
                $check['email'] = $data['email'];
            }
            if(!empty($data['phone'])){
                $check['phone'] = $data['phone'];
            }
            $user = User::where($check)->firstOrFail();
            return $user;
        
    }
    public function pass_valid($data, $user){
        if(!Hash::check($data['password'], $user->password)){
                throw new Exception('Invalid password');
            }
            if($user->is_verified == false){
                 throw new Exception('User not verified');
            }
            return true;
    }
    public function change_password($data, $user){
        if(!Hash::check($data['old_password'], $user->password)){
            throw new Exception('Invalid old password');
        }
        if($data['old_password'] == $data['password']){
            throw new Exception('New password cannot be same as old password');
        }
        return $user;
    }
    
}