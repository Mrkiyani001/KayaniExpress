<?php
namespace App\Services;

use App\Models\Shop;
use App\Repository\ShopRepo;
use App\Traits\UploadTraits;
use Exception;
use Illuminate\Support\Str;

class ShopService{
    use UploadTraits;
    public function __construct(public ShopRepo $shopRepo){
    }
    public function apply($data , $user , $request){
        $logoName = null;
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $logoName = $this->upload($file, 'shops/logos');
        }
        $check_slug = Shop::where('slug', Str::slug($data['shop_name']))->count();
        if($check_slug > 0){
            $slug = Str::slug($data['shop_name']) . '-' . ($check_slug + 1);
        }else{
            $slug = Str::slug($data['shop_name']);
        }
        return $this->shopRepo->apply($data , $user , $logoName , $slug);
    }
    public function approve($data){
        $shop = $this->shopRepo->approve($data);
        return $shop;
    }
    public function reject($data){
        $shop = $this->shopRepo->reject($data);
        return $shop;
    }
    public function suspend($data){
        $shop = $this->shopRepo->suspend($data);
        return $shop;
    }
    public function unsuspend($data){
        $shop = $this->shopRepo->unsuspend($data);
        return $shop;
    }
    public function myshop($user){
        $shop = $this->shopRepo->myshop($user);
        return $shop;
    }
    public function updateShop($data , $user , $request){
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $data['logo'] = $this->upload($file, 'shops/logos');
        }
        if($request->hasFile('banner')){
            $file = $request->file('banner');
            $data['banner'] = $this->upload($file, 'shops/banners');
        }
        if(isset($data['shop_name'])){
            $check_slug = Shop::where('slug', Str::slug($data['shop_name']))->where('id', '!=', $data['shop_id'])->count();
            if($check_slug > 0){
                $slug = Str::slug($data['shop_name']) . '-' . $check_slug;
            }else{
                $slug = Str::slug($data['shop_name']);
            }
            $data['slug'] = $slug;
        }
        $shop = $this->shopRepo->updateShop($data , $user);
        return $shop;
    }
    public function shopdetail($slug){
        $shop = $this->shopRepo->shopdetail($slug);
        return $shop;
    }
    public function shoplist($user){
        $shop = $this->shopRepo->shoplist($user);
        return $shop;
    }
}