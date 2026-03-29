<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequests\ChangePassRequest;
use App\Http\Requests\AuthRequests\CheckAvailableRequest;
use App\Http\Requests\AuthRequests\ForgetPassRequest;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\ResendOtpRequest;
use App\Http\Requests\AuthRequests\ResetPassRequest;
use App\Http\Requests\AuthRequests\SignupRequest;
use App\Http\Requests\AuthRequests\VerifyOtpRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\RedisBloomFilter;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private $bloomFilter;
    private $authService;
    
    public function __construct(RedisBloomFilter $bloomFilter, AuthService $authService) {
        $this->bloomFilter = $bloomFilter;
        $this->authService = $authService;
    }
    public function check_availability(CheckAvailableRequest $request){
        $data = $request->validated();
        try{
            $identify = $data['email'] ?? $data['phone'];
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
        $user = $this->authService->create_user($data);
        $this->bloomFilter->add($user->email ?? $user->phone); // Store email in bloom filter to optimize later lookups
        DB::commit();
        $user->load('role');
        return $this->Response('success', 'User created successfully', ['user' => $user, 'otp' => $user->otp], 200);
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
            $user = $this->authService->verify_otp($data);
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
            $user = $this->authService->resend_otp($data);
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
            $user = $this->authService->login($data);
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
            $user = $this->authService->forget_password($data);
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
            $user = $this->authService->reset_password($data);
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
            $user = $this->authService->change_password($data, $user);
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