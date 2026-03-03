<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Shop;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends BaseController
{
    public function create_product(Request $request){
        $this->ValidateRequest($request,[
            'shop_id' => 'required|exists:shops,id',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file'=>'nullable|array',
            'file.*' => 'file|mimes:mp4,mov,avi,wmv,flv,mkv,webm,3gp,jpeg,png,gif,webp,bmp,svg,heic,heif|max:102400',
            'is_featured' => 'nullable|boolean',
            // SKUs validation
            'skus' => 'required|array|min:1',
            'skus.*.price' => 'required|numeric|min:0',
            'skus.*.discounted_price' => 'nullable|numeric|min:0',
            'skus.*.stock_qty' => 'required|integer|min:0',
            'skus.*.attribute_values' => 'required|array', // e.g. {"Color": "Red", "Size": "XL"}
        ]);
        try{
        DB::beginTransaction();
        $user =auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Customer'])){
            return $this->NotAllowed();
        }
        $shop = Shop::where('user_id', $user->id)->first();
        if(!$shop){
            return $this->Response(false, 'Shop not found',[], 404);
        }
        $category = Category::where('id', $request->category_id)->first();
        if(!$category){
            return $this->Response(false, 'Category not found',[], 404);
        }
        $brand = Brand::where('id', $request->brand_id)->first();
        if(!$brand){
            return $this->Response(false, 'Brand not found',[], 404);
        }
        $check_slug = Product::where('slug', Str::slug($request->name))->count();
        if($check_slug > 0){
            $slug = Str::slug($request->name).'-'.($check_slug + 1);
        }else{
            $slug = Str::slug($request->name);
        }
        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description ?? null,
            'is_featured' => $request->is_featured ?? false,
        ]);

        // Files upload
        if($request->hasFile('file')){
            $files = $request->file('file');
            foreach($files as $file){
                $this->product_file($file, 'products', $product);
            }
            // Pehli image ko main mark karo (loop ke bahar)
            $product->attachment()->oldest()->update(['is_main' => true]);
        }
        foreach($request->skus as $sku){
            $sku_code = Str::slug($product->name).'-'.Str::slug(implode('-', $sku['attribute_values']));
            $check_sku = ProductSku::where('sku_code', $sku_code)->count();
            if($check_sku > 0){
                $sku_code = $sku_code.'-'.($check_sku + 1);
            }
            ProductSku::create([
                'product_id' => $product->id,
                'sku_code' => $sku_code,
                'price' => $sku['price'],
                'discounted_price' => $sku['discounted_price'] ?? 0,
                'stock_qty' => $sku['stock_qty'],
                'attribute_values' => json_encode($sku['attribute_values']),
            ]);
        }
        DB::commit();
        $product->load('attachment', 'skus');
        return $this->Response(true, 'Product created successfully', $product, 201);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
}
