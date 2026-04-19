<?php
namespace App\Repository;

use App\Models\SellerWallet;
use App\Models\Shop;
use App\Services\RabbitMQService;
use Exception;
use Illuminate\Support\Str;

class ShopRepo{
    private $rabbitmqService;
    public function __construct(RabbitMQService $rabbitmqService){
        $this->rabbitmqService = $rabbitmqService;
    }
    public function apply($data , $user , $logoName , $slug){
        if($user->shop){
            throw new Exception('You already have a shop');
        }
        $shop = Shop::create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'],
                'slug' => $slug,
                'logo' => $logoName ?? null,
                'description' => $data['description'],
                'phone' => $data['phone'],
                'city_id' => $data['city_id'],
                'status' => 'pending',
            ]);
            $data = [
                'event'    => 'shop.applied',
                'shop_id' => $shop->id,
                'user_id'  => $shop->user_id,
                'shop_name' => $shop->shop_name,
                'timestamp'=> now()->toISOString(),
            ];
            $this->rabbitmqService->publish('shop.applied', 'shop.applied', $data);
            return $shop;
    }
    public function approve($data){
        $shop = Shop::findOrFail($data['shop_id']);
            if($shop->status != 'pending'){
                throw new Exception('Shop is not in pending status. Current Status: '.$shop->status);
            }
            $shop->status = 'approved';
            $shop->verified_at = now();
            $shop->user->syncRoles('Seller');
            $shop->save();
            SellerWallet::create([
                'shop_id' => $shop->id,
            ]);
            $shop->load('user.roles');
            $data = [
                'event'    => 'shop.approved',
                'shop_id' => $shop->id,
                'user_id'  => $shop->user_id,
                'shop_name' => $shop->shop_name,
                'timestamp'=> now()->toISOString(),
            ];
            $this->rabbitmqService->publish('shop.approved', 'shop.approved', $data);
            return $shop;   
    }
    public function reject($data){
        $shop = Shop::findOrFail($data['shop_id']);
        if($shop->status != 'pending'){
            throw new Exception('Shop is not in pending status. Current Status: '.$shop->status);
        }
        $shop->status = 'rejected';
        $shop->user->syncRoles('Customer');
        $shop->load('user.roles');
        $shop->delete();
        return $shop;
    }
    public function suspend($data){
        $shop =Shop::findOrFail($data['shop_id']);
        if($shop->status != 'approved'){
            throw new Exception('Shop is not in approved status. Current Status: '.$shop->status);
        }
        if($shop->status == 'suspended'){
            throw new Exception('Shop is already suspended');
        }
        $shop->status = 'suspended';
        $shop->user->syncRoles('Customer');
        $shop->load('user.roles');
        $shop->save();
       
        return $shop;
    }
    public function unsuspend($data){
        $shop =Shop::findOrFail($data['shop_id']);
        if($shop->status != 'suspended'){
            throw new Exception('Shop is not in suspended status. Current Status: '.$shop->status);
        }
        $shop->status = 'approved';
        $shop->user->syncRoles('Seller');
        $shop->load('user.roles');
        $shop->save();
        return $shop;
    }
    public function myshop($user){
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        return $shop;
    }
    public function updateShop($data , $user){
        $shop = Shop::findOrFail($data['shop_id']);
        if($user->id != $shop->user_id){
            throw new Exception('You are not authorized to update this shop');
        }
        if($shop->status != 'approved'){
            throw new Exception('You are not authorized to update this shop');
        }
        $shop->update($data);
        return $shop;
    }
    public function shopdetail($slug){
        $shop = Shop::where('slug', $slug)->where('status', 'approved')->firstOrFail();
        $shop->load('city');
        return $shop;
    }
    public function shoplist($user){
        if($user->hasRole(['Super Admin', 'Admin'])){
            $shop = Shop::all();
            $shop->load('city');
        }else{
            $shop = Shop::where('user_id', $user->id)->where('status', 'approved')->get();
            $shop->load('city');
        }
        return $shop;
    }
}