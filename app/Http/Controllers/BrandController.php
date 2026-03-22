<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\CreateRequest;
use App\Http\Requests\Brand\UpdateRequest;
use App\Http\Requests\Brand\DeleteRequest;
use App\Models\Brand;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandController extends BaseController
{
    public function create_brand(CreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin', 'Admin'])){
            return $this->NotAllowed();
        }
        if($request->hasFile('logo')){
            $logo = $request->file('logo');
            $logo_path = $this->upload($logo , 'Brands/Logos');
        }
        $check_slug = Brand::where('slug', Str::slug($data['name']))->count();
        if($check_slug > 0){
            $slug = Str::slug($data['name']) . '-' . ($check_slug + 1);
        }else{
            $slug = Str::slug($data['name']);
        }
        $brand = Brand::create([
            'name' => $data['name'],
            'slug' => $slug,
            'logo' => $logo_path ?? null,
            'status' => $data['status'],
        ]);
        DB::commit();
        return $this->Response(true, 'Brand created successfully', $brand, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Brand created failed', $e->getMessage(), 500);
    }
    }
    public function update_brand(UpdateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin', 'Admin'])){
            return $this->NotAllowed();
        }
        $brand = Brand::findOrFail($data['brand_id']);
        if($request->hasFile('logo')){
            $logo = $request->file('logo');
            $logo_path = $this->upload($logo , 'Brands/Logos');
        }
        if(!empty($data['name'])){
            $check_slug = Brand::where('slug', Str::slug($data['name']))->where('id', '!=', $brand->id)->count();
            if($check_slug > 0){
                $slug = Str::slug($data['name']) . '-' . ($check_slug + 1);
            }else{
                $slug = Str::slug($data['name']);
            }
        }
        $brand->update([
            'name' => $data['name'] ?? $brand->name,
            'slug' => $slug ?? $brand->slug,
            'logo' => $logo_path ?? $brand->logo,
            'status' => $data['status'] ?? $brand->status,
        ]);
        DB::commit();
        return $this->Response(true, 'Brand updated successfully', $brand, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Brand updated failed', $e->getMessage(), 500);
    }
    }
    public function delete_brand(DeleteRequest $request){
        $data = $request->validated();
        try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin', 'Admin'])){
            return $this->NotAllowed();
        }
        $brand = Brand::findOrFail($data['brand_id']);
        $brand->delete();
        return $this->Response(true, 'Brand deleted successfully', [], 200);
    }catch(Exception $e){
        return $this->Response(false, 'Brand deleted failed', $e->getMessage(), 500);
    }
    }
    public function get_all_brands(){
        try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Super Admin', 'Admin'])){
            $brands = Brand::all();
        }else{
            $brands = Brand::where('status', 'active')->get();
        }
        return $this->Response(true, 'Brands fetched successfully', $brands, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Brands fetched failed', $e->getMessage(), 500);
    }
    }
    public function get_brand($slug){    
        try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $check_slug =Brand::where('slug', $slug)->where('status', 'active')->first();
        if(!$check_slug){
            return $this->Response(false, 'Brand not found', [], 404);
        }
        return $this->Response(true, 'Brand fetched successfully', $check_slug, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Brand fetched failed', $e->getMessage(), 500);
    }
    }
}
