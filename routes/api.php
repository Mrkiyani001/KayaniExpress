<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AttributesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SellerWalletController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishListController;
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

    //Shops
    Route::prefix('shop')->group(function () {
        Route::post('/approve', [ShopController::class, 'approve']);
        Route::post('/reject', [ShopController::class, 'reject']);
        Route::post('/suspend', [ShopController::class, 'suspend']);
        Route::post('/unsuspend', [ShopController::class, 'unsuspend']);
        Route::get('/list', [ShopController::class, 'shoplist']);
    });

    //Categories
    Route::prefix('category')->group(function () {
        Route::post('/create', [CategoryController::class, 'create_category']);
        Route::put('/update', [CategoryController::class, 'update_category']);
        Route::delete('/delete', [CategoryController::class, 'delete_category']);
    });

    //Brands
    Route::prefix('brand')->group(function () {
        Route::post('/create', [BrandController::class, 'create_brand']);
        Route::put('/update', [BrandController::class, 'update_brand']);
        Route::delete('/delete', [BrandController::class, 'delete_brand']);
    });

    //Attributes
    Route::prefix('attribute')->group(function () {
        Route::post('/create', [AttributesController::class, 'create_attribute']);
        Route::put('/update', [AttributesController::class, 'update_attribute']);
        Route::delete('/delete', [AttributesController::class, 'delete_attribute']);
    });

    //Attribute Values
    Route::prefix('attribute-value')->group(function () {
        Route::post('/create', [AttributesController::class, 'create_attribute_value']);
        Route::put('/update', [AttributesController::class, 'update_attribute_value']);
        Route::delete('/delete', [AttributesController::class, 'delete_attribute_value']);
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

//Shop Routes
Route::prefix('shop')->group(function () {
    Route::post('/apply', [ShopController::class, 'apply']);
    Route::get('/myshop', [ShopController::class, 'myshop']);
    Route::put('/update', [ShopController::class, 'updateShop']);
});

//Seller Wallet Routes
Route::prefix('seller/wallet')->group(function () {
    Route::get('/balance', [SellerWalletController::class, 'balance']);
    Route::post('/withdraw', [SellerWalletController::class, 'withdraw']);
});

//Categories Routes
Route::prefix('category')->group(function () {
    Route::get('/list', [CategoryController::class, 'get_categories']);
    Route::get('/list/{slug}', [CategoryController::class, 'get_category']);
});

//Brands Routes
Route::prefix('brand')->group(function () {
    Route::get('/list', [BrandController::class, 'get_all_brands']);
    Route::get('/list/{slug}', [BrandController::class, 'get_brand']);
});

//Attributes Routes
Route::prefix('attribute')->group(function () {
    Route::get('/list', [AttributesController::class, 'get_attributes']);
});

//Attribute Values Routes
Route::prefix('attribute-value')->group(function () {
    Route::get('/list', [AttributesController::class, 'get_attribute_values']);
});

Route::prefix('product')->group(function () {
    Route::post('/create', [ProductController::class, 'create_product']);
    Route::put('/update', [ProductController::class, 'update']);
    Route::delete('/delete', [ProductController::class, 'delete_product']);
    Route::get('/my-products', [ProductController::class, 'my_products']);
});

Route::prefix('customer/cart')->group(function () {
    Route::post('/create', [CartController::class, 'add_to_cart']);
    Route::put('/update', [CartController::class, 'update_cart']);
    Route::delete('/delete', [CartController::class, 'delete_cart']);
    Route::get('/list', [CartController::class, 'get_cart']);
});

Route::prefix('customer/wishlist')->group(function () {
    Route::post('/create', [WishListController::class, 'add_to_wishlist']);
    Route::delete('/delete', [WishListController::class, 'delete_wishlist']);
    Route::get('/list', [WishListController::class, 'get_wishlist']);
});


});

// Public Routes
Route::get('/shop/{slug}', [ShopController::class, 'shopdetail']);
Route::get('/product/{slug}', [ProductController::class, 'product_detail']);
Route::post('/category/products', [ProductController::class, 'category_wise']);
Route::post('/shop/products', [ProductController::class, 'shop_wise']);
Route::post('/brand/products', [ProductController::class, 'brand_wise']);
Route::get('/products', [ProductController::class, 'products']);

