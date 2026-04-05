<?php 
namespace App\Services;

use App\Notifications\SendOtpNotification;
use App\Repository\AuthRepo;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService{
    private $authRepo;
    public function __construct(AuthRepo $authRepo){
        $this->authRepo = $authRepo;
    }
    public function create_user($data){
        try{
            $user = $this->authRepo->create_user($data);
            if ($user->email) {
            $user->notify(new SendOtpNotification($user->otp));
            }
            if ($user->phone) {
            Log::info("SMS to {$user->phone}: Your OTP is {$user->otp}");
            }
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function find_user($data){
        try{
            $user = $this->authRepo->find_user($data);
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function verify_otp($data){
        try{
            $user = $this->find_user($data);
            if($user->otp != $data['otp']){
                throw new Exception('Invalid OTP');
            }
            if($user->otp_expires_at < now()){
                throw new Exception('OTP expired');
            }
            $user->is_verified = true;
            $user->otp = $data['otp'];
            $user->otp_expires_at = null;
            $user->save();
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function resend_otp($data){
        try{
            $user = $this->find_user($data);
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            if ($user->email) {
                $user->notify(new SendOtpNotification($user->otp));
            } 
            
            if ($user->phone) {
                 Log::info("SMS to {$user->phone}: Your OTP is {$user->otp}");
            }
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function login($data){
        try{
            $user = $this->find_user($data);
            $this->authRepo->pass_valid($data, $user);
            $user->remember_token = Str::random(40);
            $user->save();
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function forget_password($data){
        try{
            $user = $this->find_user($data);
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            if ($user->email) {
                $user->notify(new SendOtpNotification($user->otp));
            } 
            
            if ($user->phone) {
                 Log::info("SMS to {$user->phone}: Your OTP is {$user->otp}");
            }
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function reset_password($data){
        try{
            $user = $this->find_user($data);
            if($user->otp != $data['otp']){
                throw new Exception('Invalid OTP');
            }
            if($user->otp_expires_at < now()){
                throw new Exception('OTP expired');
            }
            $user->password = Hash::make($data['password']);
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function change_password($data, $user){
        try{
            $user = $this->authRepo->change_password($data, $user);
            $user->password = Hash::make($data['password']);
            $user->save();
            return $user;
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}