<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    public function signup(Request $request){
        $this->ValidateRequest($request, [
            'name' => 'required|string|max:20',
            'email' => 'nullable|required_without:phone|email|unique:users,email',
            'phone' => 'nullable|required_without:email|unique:users,phone',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6'
        ]);
        try{
        DB::beginTransaction();
        $otp = rand(100000, 999999);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        if ($user->email) {
            $user->notify(new SendOtpNotification($otp));
        } 
        
        if ($user->phone) {
             Log::info("SMS to {$user->phone}: Your OTP is {$otp}");
        }

        DB::commit();
        return $this->Response('success', 'User created successfully', ['user' => $user, 'otp' => $otp], 200);
    }
    catch(Exception $e){
        DB::rollBack();
        return $this->Response('error', 'User creation failed', $e->getMessage(), 500);
    }
}
    public function VerifyOtp(Request $request){
        $this->ValidateRequest($request, [
            'email' => 'nullable|required_without:phone|email|exists:users,email',
            'phone' => 'nullable|required_without:email|exists:users,phone',
            'otp' => 'required|numeric|digits:6',
        ]);
        try{
            DB::beginTransaction();
            $user = User::where('email', $request->email)
                        ->orWhere('phone', $request->phone)
                        ->first();

            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }
            if($user->otp != $request->otp){
                return $this->Response('error', 'Invalid OTP', null, 400);
            }
            if($user->otp_expires_at < now()){
                return $this->Response('error', 'OTP expired', null, 400);
            }
            
            $user->is_verified = true;
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
            DB::commit();
            return $this->Response('success', 'User verified successfully', ['user' => $user, 'token' => $token], 200);
        }
        catch(Exception $e){
            DB::rollBack();
            return $this->Response('error', 'User verification failed', $e->getMessage(), 500);
        }
    }

    public function resendOtp(Request $request){
        $this->ValidateRequest($request, [
            'email' => 'nullable|required_without:phone|email|exists:users,email',
            'phone' => 'nullable|required_without:email|exists:users,phone',
        ]);
        try{
            DB::beginTransaction();
            $user = User::where('email', $request->email)
                        ->orWhere('phone', $request->phone)
                        ->first();

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

    public function login(Request $request){
        $this->ValidateRequest($request, [
            'email' => 'nullable|required_without:phone|email|exists:users,email',
            'phone' => 'nullable|required_without:email|exists:users,phone',
            'password' => 'required|min:6',
        ]);
        try{
            $user = User::where('email', $request->email)
                        ->orWhere('phone', $request->phone)
                        ->first();
            if(!$user){
                return $this->Response('error', 'User not found', null, 404);
            }
            if(!Hash::check($request->password, $user->password)){
                return $this->Response('error', 'Invalid password', null, 401);
            }
            if($user->is_verified == false){
                 return $this->Response('error', 'User not verified', null, 403);
            }
            $token = auth('api')->login($user);
            return $this->ResponseWithToken($token);
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
}