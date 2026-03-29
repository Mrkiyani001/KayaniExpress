<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\CreateRequest;
use App\Http\Requests\Brand\UpdateRequest;
use App\Http\Requests\Brand\DeleteRequest;
use App\Models\Brand;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Services\BrandService;

class BrandController extends BaseController
{
    private $BrandService;
    public function __construct(BrandService $BrandService){
        $this->BrandService = $BrandService;
    }
    public function create_brand(CreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $this->authorize('checkrole', Role::class);
        $brand = $this->BrandService->create_brand($data , $request);
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
        $this->authorize('checkrole', Role::class);
        $brand = $this->BrandService->update_brand($data , $request);
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
            DB::beginTransaction();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $this->authorize('checkrole', Role::class);
        $brand = $this->BrandService->delete_brand($data);
        DB::commit();
        return $this->Response(true, 'Brand deleted successfully', [], 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Brand deleted failed', $e->getMessage(), 500);
    }
    }
    public function get_all_brands(){
        try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $brands = $this->BrandService->get_all_brands($user);
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
        $brand = $this->BrandService->get_brand($slug);
        return $this->Response(true, 'Brand fetched successfully', $brand, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Brand fetched failed', $e->getMessage(), 500);
    }
    }
}
