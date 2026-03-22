<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequests\ChangePassRequest;
use App\Http\Requests\AuthRequests\ForgetPassRequest;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\ResendOtpRequest;
use App\Http\Requests\AuthRequests\ResetPassRequest;
use App\Http\Requests\AuthRequests\SignupRequest;
use App\Http\Requests\AuthRequests\VerifyOtpRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Services\RedisBloomFilter;

class AuthController extends BaseController
{
    private $bloomFilter;
    
    public function __construct(RedisBloomFilter $bloomFilter) {
        $this->bloomFilter = $bloomFilter;
    }
    public function check_availability(Request $request){
        $this->ValidateRequest($request, [
            'email' => 'nullable|required_without:phone|email',
            'phone' => 'nullable|required_without:email|numeric',
        ]);
        try{
            $identify = $request->input('email') ?? $request->input('phone');
            if($identify){
                if($this->bloomFilter->has($identify)){
                    return $this->Response('error', 'User already exists', null, 409);
                }
            }
            return $this->Response('success', 'User is available', null, 200);
        }
        catch(Exception $e){
            return $this->Response('error', 'User check failed', $e->getMessage(), 500);
        }
    }
    public function signup(SignupRequest $request){
        $data = $request->validated();
        try{    
        DB::beginTransaction();
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

        if ($user->email) {
            $user->notify(new SendOtpNotification($otp));
          
        } 
          $this->bloomFilter->add($user->email ?? $user->phone); // Store email in bloom filter to optimize later lookups
        
        if ($user->phone) {
             Log::info("SMS to {$user->phone}: Your OTP is {$otp}");
        }

        DB::commit();
        $user->load('role');
        return $this->Response('success', 'User created successfully', ['user' => $user, 'otp' => $otp], 200);
    }
    catch(Exception $e){
        DB::rollBack();
        return $this->Response('error', 'User creation failed', $e->getMessage(), 500);
    }
}
    public function VerifyOtp(VerifyOtpRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $check = [];
            if(!empty($data['email'])){
                $check['email'] = $data['email'];
            }
            if(!empty($data['phone'])){
                $check['phone'] = $data['phone'];
            }
            $user = User::where($check)->first();

            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }
            if($user->otp != $data['otp']){
                return $this->Response('error', 'Invalid OTP', null, 400);
            }
            if($user->otp_expires_at < now()){
                return $this->Response('error', 'OTP expired', null, 400);
            }
            
            $user->is_verified = true;
            $user->otp = $data['otp'];
            $user->otp_expires_at = null;
            $user->save();
            DB::commit();
            $token = auth('api')->login($user);
            $user->load('role');
            return $this->ResponseWithToken($token, $user);
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->Response('error', 'User verification failed', $e->getMessage(), 500);
        }
    }

    public function resendOtp(ResendOtpRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $check = [];
            if(!empty($data['email'])){
                $check['email'] = $data['email'];
            }
            if(!empty($data['phone'])){
                $check['phone'] = $data['phone'];
            }
            $user = User::where($check)->first();

            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }

            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            if ($user->email) {
                $user->notify(new SendOtpNotification($otp));
            } 
            
            if ($user->phone) {
                 Log::info("SMS to {$user->phone}: Your OTP is {$otp}");
            }
            DB::commit();
            return $this->Response('success', 'OTP sent successfully', null, 200);
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->Response('error', 'OTP resend failed', $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request){
        $data = $request->validated();
        try{
            $check = [];
            if(!empty($data['email'])){
                $check['email'] = $data['email'];
            }
            if(!empty($data['phone'])){
                $check['phone'] = $data['phone'];
            }
            $user = User::where($check)->first();
            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }
            if(!Hash::check($data['password'], $user->password)){
                return $this->Response('error', 'Invalid password', null, 401);
            }
            if($user->is_verified == false){
                 return $this->Response('error', 'User not verified', null, 403);
            }
            $token = auth('api')->login($user);
            $user->load('role');
            return $this->ResponseWithToken($token, $user);
        }
        catch(Exception $e){
            return $this->Response('error', 'Login failed', $e->getMessage(), 500);
        }
    }
    public function logout(){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            auth('api')->logout();
            return $this->Response('success', 'Logout successfully', null, 200);
        }
        catch(Exception $e){
            return $this->Response('error', 'Logout failed', $e->getMessage(), 500);
        }
    }

    public function RefreshToken(){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $token = auth('api')->refresh();
            return $this->ResponseWithToken($token);
        }
        catch(Exception $e){
            return $this->Response('error', 'Refresh token failed', $e->getMessage(), 500);
        }
    }
    public function forgetPassword(ForgetPassRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $check = [];
            if(!empty($data['email'])){
                $check['email'] = $data['email'];
            }
            if(!empty($data['phone'])){
                $check['phone'] = $data['phone'];
            }
            $user = User::where($check)->first();
            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            if ($user->email) {
                $user->notify(new SendOtpNotification($otp));
            } 
            if ($user->phone) {
                 Log::info("SMS to {$user->phone}: Your OTP is {$otp}");
            }
            DB::commit();
            return $this->Response('success', 'OTP sent successfully', null, 200);
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->Response('error', 'OTP resend failed', $e->getMessage(), 500);
        }
    }
    public function resetPassword(ResetPassRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $check = [];
            if(!empty($data['email'])){
                $check['email'] = $data['email'];
            }
            if(!empty($data['phone'])){
                $check['phone'] = $data['phone'];
            }
            $user = User::where($check)->first();
            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }
            if($user->otp != $data['otp']){
                return $this->Response('error', 'Invalid OTP', null, 400);
            }
            if($user->otp_expires_at < now()){
                return $this->Response('error', 'OTP expired', null, 400);
            }
            $user->password = Hash::make($data['password']);
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
            DB::commit();
            return $this->Response('success', 'Password reset successfully', null, 200);
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->Response('error', 'Password reset failed', $e->getMessage(), 500);
        }
    }
    public function changepassword(ChangePassRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!Hash::check($request->old_password, $user->password)){
                return $this->Response('error', 'Invalid old password', null, 400);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            auth('api')->logout();
            DB::commit();
            return $this->Response('success', 'Password changed successfully. Please login again.', null, 200);
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->Response('error', 'Password change failed', $e->getMessage(), 500);
        }
    }
}