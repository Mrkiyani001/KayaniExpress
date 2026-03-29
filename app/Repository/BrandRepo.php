<?php
namespace App\Repository;

use App\Models\Brand;
use Exception;

class BrandRepo{
    public function create_brand($data , $logo_path , $slug){
        try{
            $brand = Brand::create([
                'name' => $data['name'],
                'slug' => $slug,
                'logo' => $logo_path,
                'status' => $data['status'],
            ]);
            return $brand;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function update_brand($data){
        try{
            $brand = Brand::findOrFail($data['brand_id']);
            $brand->update($data);
            return $brand;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function delete_brand($data){
        try{
            $brand = Brand::findOrFail($data['brand_id']);
            $brand->delete();
            return $brand;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function get_all_brands($user){
        try{
            if($user->hasRole(['Super Admin', 'Admin'])){
                $brands = Brand::all();
            }else{
                $brands = Brand::where('status', 'active')->get();
            }
            return $brands;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function get_brand($slug){
        try{
            $brand = Brand::where('slug', $slug)->where('status', 'active')->firstOrFail();
            return $brand;
        }catch(Exception $e){
            throw $e;
        }
    }
}