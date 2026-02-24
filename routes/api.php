<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
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
        });
    });

    Route::prefix('permission')->group(function () {
        Route::get('/all', [RolesController::class, 'getAllPermissions']);
        Route::post('/create', [RolesController::class, 'createPermission']);
        Route::post('/delete', [RolesController::class, 'deletePermission']);
    });

    Route::post('user/roles-permissions', [RolesController::class, 'getUserRolePermission']);



    
    Route::get('/cities', [CityController::class, 'list']); // can access by any user
    Route::post('/areas', [AreaController::class, 'city_wise_list']); // can access by any user
    
// Admin Routes
    Route::prefix('admin')->group(function () {
    //Cities
    Route::prefix('city')->group(function () {
        Route::post('/create', [CityController::class, 'create']);
        Route::put('/update', [CityController::class, 'update']);
        Route::delete('/delete', [CityController::class, 'delete']);
        Route::post('/filter', [CityController::class, 'city_filter']);
        
    });

    //Areas
    Route::prefix('area')->group(function () {
        Route::post('/create', [AreaController::class, 'create']);
        Route::put('/update', [AreaController::class, 'update']);
        Route::delete('/delete', [AreaController::class, 'delete']);
        Route::post('/filter', [AreaController::class, 'area_filter']);
    });
});

//Address Routes
Route::prefix('address')->group(function () {
    Route::post('/create', [AddressController::class, 'create']);
    Route::put('/update', [AddressController::class, 'update']);
    Route::delete('/delete', [AddressController::class, 'delete']);
    Route::get('/list', [AddressController::class, 'list']);
    Route::put('/set-default', [AddressController::class, 'setDefault']);
});
});
