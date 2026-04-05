<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\Brand_wiseRequest;
use App\Http\Requests\Product\Cat_wiseRequest;
use App\Http\Requests\Product\CreateRequest;
use App\Http\Requests\Product\DeleteRequest;
use App\Http\Requests\Product\FilterRequest;
use App\Http\Requests\Product\Shop_wiseRequest;
use App\Http\Requests\Product\UpdateRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ProductService;
class ProductController extends BaseController
{
    private $productService;
    public function __construct(ProductService $productService){
        $this->productService = $productService;
    }
    public function create_product(CreateRequest $request){
        $data = $request->validated();
        try{
        DB::beginTransaction();
        $user =auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Customer'])){
            return $this->NotAllowed();
        }
        $product = $this->productService->create_product($data , $user ,$request);
        DB::commit();
        return $this->Response(true, 'Product created successfully', $product, 201);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function update(UpdateRequest $request){
    $data = $request->validated();
    try{
        DB::beginTransaction();
        $user =auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Customer'])){
            return $this->NotAllowed();
        }
        $product = $this->productService->update_product($data , $user ,$request);
        DB::commit();
        return $this->Response(true, 'Product updated successfully', $product, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function delete_product(DeleteRequest $request){
    $data = $request->validated();
    try{
        DB::beginTransaction();
        $user =auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Customer'])){
            return $this->NotAllowed();
        }
        $product = $this->productService->delete_product($data , $user);
        DB::commit();
        return $this->Response(true, 'Product deleted successfully',[], 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function my_products(Request $request){
    try{
        $limit =(int) $request->input('limit', 10) ;
        $user =auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Customer'])){
            return $this->NotAllowed();
        }
        $product = $this->productService->my_product($user , $limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function products(FilterRequest $request){
    try{
        $limit =(int) $request->input('limit', 10) ;
        $product = $this->productService->products($limit , $request);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
} 
public function product_detail($slug){
    try{
        $product = $this->productService->product_detail($slug);
        return $this->Response(true, 'Product fetched successfully', $product, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function category_wise(Cat_wiseRequest $request){
    try{
        $data = $request->validated();
        $limit =(int) $request->input('limit', 10) ;
        $product = $this->productService->category_wise($data , $limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function shop_wise(Shop_wiseRequest $request){
    try{
        $data = $request->validated();
        $limit =(int) $request->input('limit', 10) ;
        $product = $this->productService->shop_wise($data , $limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function brand_wise(Brand_wiseRequest $request){
    try{
        $data = $request->validated();
        $limit =(int) $request->input('limit', 10) ;
        $product = $this->productService->brand_wise($data , $limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
}
