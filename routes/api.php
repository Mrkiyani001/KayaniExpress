<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Route;

Route::post('signup', [AuthController::class, 'signup']);
Route::post('verify/otp', [AuthController::class, 'VerifyOtp']);
Route::post('resend/otp', [AuthController::class, 'resendOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forget/password', [AuthController::class, 'forgetPassword']);
Route::post('reset/password', [AuthController::class, 'resetPassword']);
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('refresh/token', [AuthController::class, 'RefreshToken']);
    Route::post('change/password', [AuthController::class, 'changepassword']); // Keeping original casing as seen in AuthController
    
    // Role Management Routes
    Route::prefix('role')->group(function () {
        Route::post('/create', [RolesController::class, 'createRole']);
        Route::post('/delete', [RolesController::class, 'deleteRole']);
        Route::post('/assign', [RolesController::class, 'assignRole']);
        Route::post('/unassign', [RolesController::class, 'unassignRole']);
    });
});
