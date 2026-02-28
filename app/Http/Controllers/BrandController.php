<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandController extends BaseController
{
    public function create_brand(Request $request){
        $this->ValidateRequest($request,[
            'name'=>'required|string|max:255',
            'logo'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'=>'required|in:active,inactive',
        ]);
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
            $logoname = $this->upload($logo , 'Brands/Logos');
        }
        $check_slug = Brand::where('slug', Str::slug($request->name))->count();
        if($check_slug > 0){
            $slug = Str::slug($request->name) . '-' . ($check_slug + 1);
        }else{
            $slug = Str::slug($request->name);
        }
        $brand = Brand::create([
            'name' => $request->name,
            'slug' => $slug,
            'logo' => $logoname ?? null,
            'status' => $request->status,
        ]);
        DB::commit();
        return $this->Response(true, 'Brand created successfully', $brand, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Brand created failed', $e->getMessage(), 500);
    }
    }
    public function update_brand(Request $request){
        $this->ValidateRequest($request,[
            'brand_id'=>'required|exists:brands,id',
            'name'=>'nullable|string|max:255',
            'logo'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'=>'nullable|in:active,inactive',
        ]);
        try{
            DB::beginTransaction();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin', 'Admin'])){
            return $this->NotAllowed();
        }
        $brand = Brand::findOrFail($request->brand_id);
        if($request->hasFile('logo')){
            $logo = $request->file('logo');
            $logoname = $this->upload($logo , 'Brands/Logos');
        }
        if($request->name){
            $check_slug = Brand::where('slug', Str::slug($request->name))->where('id', '!=', $brand->id)->count();
            if($check_slug > 0){
                $slug = Str::slug($request->name) . '-' . ($check_slug + 1);
            }else{
                $slug = Str::slug($request->name);
            }
        }
        $brand->update([
            'name' => $request->name ?? $brand->name,
            'slug' => $slug ?? $brand->slug,
            'logo' => $logoname ?? $brand->logo,
            'status' => $request->status ?? $brand->status,
        ]);
        DB::commit();
        return $this->Response(true, 'Brand updated successfully', $brand, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Brand updated failed', $e->getMessage(), 500);
    }
    }
    public function delete_brand(Request $request){
        $this->ValidateRequest($request,[
            'brand_id'=>'required|exists:brands,id',
        ]);
        try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin', 'Admin'])){
            return $this->NotAllowed();
        }
        $brand = Brand::findOrFail($request->brand_id);
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
