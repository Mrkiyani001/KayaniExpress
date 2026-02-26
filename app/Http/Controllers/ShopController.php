<?php

namespace App\Http\Controllers;

use App\Models\SellerWallet;
use App\Models\Shop;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends BaseController
{
    public function apply(Request $request)
    {
        $this->ValidateRequest($request, [
            'shop_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'description' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if($user->shop){
                return $this->Response(false, 'You already have a shop',[], 400);
            }
            if($request->hasFile('logo')){
                $file = $request->file('logo');
                $logoName = $this->upload($file, 'shops/logos');
            }
            $check_slug = Shop::where('slug', Str::slug($request->shop_name))->count();
            if($check_slug > 0){
                $slug = Str::slug($request->shop_name) . '-' . ($check_slug + 1);
            }else{
                $slug = Str::slug($request->shop_name);
            }
            $shop = Shop::create([
                'user_id' => $user->id,
                'shop_name' => $request->shop_name,
                'slug' => $slug,
                'logo' => $logoName ?? null,
                'description' => $request->description ?? null,
                'phone' => $request->phone,
                'city_id' => $request->city_id,
                'status' => 'pending',
            ]);
            return $this->Response(true, 'Shop applied successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function approve(Request $request){
        $this->ValidateRequest($request, [
            'shop_id' => 'required|exists:shops,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->Response(false, 'You are not authorized to approve a shop',[], 401);
            }
            $shop = Shop::findOrFail($request->shop_id);
            if($shop->status != 'pending'){
                return $this->Response(false, 'Shop is not in pending status',[], 400);
            }
            if($shop->status == 'approved'){
                return $this->Response(false, 'Shop is already approved',[], 400);
            }
            if($shop->status == 'rejected'){
                return $this->Response(false, 'Shop is already rejected',[], 400);
            }
            if($shop->status == 'suspended'){
                return $this->Response(false, 'Shop is already suspended',[], 400);
            }
            $shop->status = 'approved';
            $shop->verified_at = now();
            $shop->user->syncRoles('Seller');
            $shop->save();
            SellerWallet::create([
                'shop_id' => $shop->id,
            ]);
            $shop->load('user.roles');
            return $this->Response(true, 'Shop approved successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function reject(Request $request){
        $this->ValidateRequest($request, [
            'shop_id' => 'required|exists:shops,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->Response(false, 'You are not authorized to reject a shop',[], 401);
            }
            $shop = Shop::findOrFail($request->shop_id);
            if($shop->status != 'pending'){
                return $this->Response(false, 'Shop is not in pending status',[], 400);
            }
            $shop->status = 'rejected';
            $shop->user->syncRoles('Customer');
            $shop->load('user.roles');
            $shop->delete();
            return $this->Response(true, 'Shop rejected successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function suspend(Request $request){
        $this->ValidateRequest($request, [
            'shop_id' => 'required|exists:shops,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->Response(false, 'You are not authorized to suspend a shop',[], 401);
            }
            $shop = Shop::findOrFail($request->shop_id);
            if($shop->status != 'approved'){
                return $this->Response(false, 'Shop is not in approved status',[], 400);
            }
            if($shop->status == 'suspended'){
                return $this->Response(false, 'Shop is already suspended',[], 400);
            }
            $shop->status = 'suspended';
            $shop->user->syncRoles('Customer');
            $shop->save();
            $shop->load('user.roles');
            return $this->Response(true, 'Shop suspended successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function unsuspend(Request $request){
        $this->ValidateRequest($request, [
            'shop_id' => 'required|exists:shops,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->Response(false, 'You are not authorized to unsuspend a shop',[], 401);
            }
            $shop = Shop::findOrFail($request->shop_id);
            if($shop->status != 'suspended'){
                return $this->Response(false, 'Shop is not in suspended status',[], 400);
            }
            $shop->status = 'approved';
            $shop->user->syncRoles('Seller');
            $shop->save();
            $shop->load('user.roles');
            return $this->Response(true, 'Shop unsuspended successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function myshop(Request $request){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = Shop::where('user_id', $user->id)->first();
            if(!$shop){
                return $this->Response(false, 'Shop not found',[], 404);
            }
            return $this->Response(true, 'Shop fetched successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function updateShop(Request $request){
        $this->ValidateRequest($request, [
            'shop_id' => 'required|exists:shops,id',
            'shop_name' => 'string|max:255',
            'description' => 'string',
            'phone' => 'string|max:255',
            'city_id' => 'exists:cities,id',
            'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'banner' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = Shop::findOrFail($request->shop_id);
            if($user->id != $shop->user_id){
                return $this->Response(false, 'You are not authorized to update this shop',[], 401);
            }
            if($shop->status != 'approved'){
                return $this->Response(false, 'You are not authorized to update this shop',[], 401);
            }
            if($request->hasFile('logo')){
                $file = $request->file('logo');
                $shop->logo = $this->upload($file, 'shops/logos');
            }
            if($request->hasFile('banner')){
                $file = $request->file('banner');
                $shop->banner = $this->upload($file, 'shops/banners');
            }
            if($request->shop_name){
                $check_slug = Shop::where('slug', Str::slug($request->shop_name))->count();
                if($check_slug > 0){
                    $slug = Str::slug($request->shop_name) . '-' . $check_slug;
                }else{
                    $slug = Str::slug($request->shop_name);
                }
                $shop->slug = $slug;
            }
            $shop->update($request->only([
                'shop_name',
                'description',
                'phone',
                'city_id',
            ]));
            return $this->Response(true, 'Shop updated successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function shopdetail($slug){
        try{
            $shop = Shop::where('slug', $slug)->where('status', 'approved')->first();
            if(!$shop){
                return $this->Response(false, 'Shop not found',[], 404);
            }
            $shop->load('city');
            return $this->Response(true, 'Shop fetched successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function shoplist(Request $request){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if($user->hasRole(['Super Admin', 'Admin'])){
                $shop = Shop::all();
            }else{
                $shop = Shop::where('user_id', $user->id)->where('status', 'approved')->get();
            }
            $shop->load('city');
            return $this->Response(true, 'Shop list fetched successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
}
