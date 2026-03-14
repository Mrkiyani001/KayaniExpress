<?php

namespace App\Http\Controllers;

use App\Helpers\DynamicFilter;
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
            return $this->Response(false, 'User has no shop. Please create a shop first.',[], 404);
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
        // Skus Start Here
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
public function update(Request $request){
    $this->ValidateRequest($request,[
        'id' => 'required|exists:products,id',
        'category_id' => 'nullable|exists:categories,id',
        'brand_id' => 'nullable|exists:brands,id',
        'name' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:1000',
        'file'=>'nullable|array',
        'file.*' => 'file|mimes:mp4,mov,avi,wmv,flv,mkv,webm,3gp,jpeg,png,gif,webp,bmp,svg,heic,heif|max:102400',
        'is_featured' => 'nullable|boolean',
        // SKUs validation
        'skus.*.id' => 'nullable|exists:product_skus,id',
        'skus' => 'nullable|array|min:1',
        'skus.*.price' => 'nullable|numeric|min:0',
        'skus.*.discounted_price' => 'nullable|numeric|min:0',
        'skus.*.stock_qty' => 'nullable|integer|min:0',
        'skus.*.attribute_values' => 'nullable|array', // e.g. {"Color": "Red", "Size": "XL"}
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
            return $this->Response(false, 'User has no shop. Please create a shop first.',[], 404);
        }
        if($request->category_id){
        $category = Category::where('id', $request->category_id)->first();
        if(!$category){
            return $this->Response(false, 'Category not found',[], 404);
        }
        }
        if($request->brand_id){
        $brand = Brand::where('id', $request->brand_id)->first();
        if(!$brand){
            return $this->Response(false, 'Brand not found',[], 404);
        }
        }
        $product = Product::where('id', $request->id)->firstOrFail();
        if($product->shop_id != $shop->id){
            return $this->Response(false, 'You are not authorized to update this product',[], 403);
        }
        if($request->name){
            $check_slug = Product::where('slug', Str::slug($request->name))->count();
            if($check_slug > 0){
                $slug = Str::slug($request->name).'-'.($check_slug + 1);
            }else{
                $slug = Str::slug($request->name);
            }
        }
        $product->update([
            'shop_id' => $product->shop_id,
            'category_id' => $request->category_id ?? $product->category_id,
            'brand_id' => $request->brand_id ?? $product->brand_id,
            'name' => $request->name ?? $product->name,
            'slug' => $slug ?? $product->slug,
            'description' => $request->description ?? $product->description,
            'is_featured' => $request->is_featured ?? $product->is_featured,
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
        if($request->skus){
            foreach($request->skus as $sku){
            if(isset($sku['id'])){
            $Exist_sku = ProductSku::where('id', $sku['id'])->first();
            $Exist_sku->update([
                'price' => $sku['price'] ?? $Exist_sku->price,
                'discounted_price' => $sku['discounted_price'] ?? $Exist_sku->discounted_price,
                'stock_qty' => $sku['stock_qty'] ?? $Exist_sku->stock_qty,
                'attribute_values' => json_encode($sku['attribute_values'] ?? $Exist_sku->attribute_values),
            ]);           
        }
        }
    }
        DB::commit();
        $product->load('attachment', 'skus');
        return $this->Response(true, 'Product updated successfully', $product, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function delete_product(Request $request){
    $this->ValidateRequest($request,[
        'id' => 'required|exists:products,id',
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
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        $product = Product::where('id', $request->id)->firstOrFail();
        if($product->shop_id != $shop->id){
            return $this->Response(false, 'Product does not belong to your shop.',[], 403);
        }
        $product->attachment()->delete();
        $product->skus()->delete();
        $product->delete();
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
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        $product = Product::where('shop_id', $shop->id)->with('attachment', 'skus', 'category', 'brand')->paginate($limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function products(Request $request){
    try{
        $limit =(int) $request->input('limit', 10) ;
        $query = Product::with('attachment', 'skus', 'category', 'brand');
        if($request->has('filters')){
            DynamicFilter::applyNestedWhereHas($request, $query);
        }
        $product = $query->paginate($limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
} 
public function product_detail($slug){
    try{
        $product = Product::where('slug', $slug)->with('attachment', 'skus', 'category', 'brand','shop')->firstOrFail();
        return $this->Response(true, 'Product fetched successfully', $product, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function category_wise(Request $request){
    try{
        $this->ValidateRequest($request,[
            'category_id' => 'required|exists:categories,id',
        ]);
        $limit =(int) $request->input('limit', 10) ;
        $product = Product::where('category_id', $request->category_id)->with('attachment', 'skus', 'category', 'brand')->paginate($limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function shop_wise(Request $request){
    try{
        $this->ValidateRequest($request,[
            'shop_id' => 'required|exists:shops,id',
        ]);
        $limit =(int) $request->input('limit', 10) ;
        $product = Product::where('shop_id', $request->shop_id)->with('attachment', 'skus', 'category', 'brand')->paginate($limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function brand_wise(Request $request){
    try{
        $this->ValidateRequest($request,[
            'brand_id' => 'required|exists:brands,id',
        ]);
        $limit =(int) $request->input('limit', 10) ;
        $product = Product::where('brand_id', $request->brand_id)->with('attachment', 'skus', 'category', 'shop', 'brand')->paginate($limit);
        $Data = $this->PaginateData($product, $product->items());
        return $this->Response(true, 'Product fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
}
