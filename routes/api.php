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
    
    // Role & Permission Management Routes
    Route::prefix('role')->group(function () {
        Route::get('/all', [RolesController::class, 'getAllRoles']);
        Route::post('/create', [RolesController::class, 'createRole']);
        Route::post('/delete', [RolesController::class, 'deleteRole']);
        Route::post('/assign', [RolesController::class, 'assignRole']);
        Route::post('/unassign', [RolesController::class, 'unassignRole']);
        
        Route::prefix('permission')->group(function () {
            Route::post('/assign', [RolesController::class, 'assignPermissionToRole']);
            Route::post('/remove', [RolesController::class, 'removePermissionFromRole']);
            Route::post('/update', [RolesController::class, 'updatePermissionForRole']);
        });
    });

    Route::prefix('permission')->group(function () {
        Route::get('/all', [RolesController::class, 'getAllPermissions']);
        Route::post('/create', [RolesController::class, 'createPermission']);
        Route::post('/delete', [RolesController::class, 'deletePermission']);
    });

    Route::get('user/roles-permissions', [RolesController::class, 'getUserRolePermission']);
});
