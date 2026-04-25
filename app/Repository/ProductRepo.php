<?php
namespace App\Repository;

use App\Helpers\DynamicFilter;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Shop;
use Exception;
class ProductRepo{
    public function create_product($data , $user ,$slug){
        
            $shop = Shop::where('user_id', $user->id)->first();
            if(!$shop){
                throw new Exception('Shop not found');
            }
            $category = Category::where('id', $data['category_id'])->first();
            if(!$category){
                throw new Exception('Category not found');
            }
            $brand = Brand::where('id', $data['brand_id'])->first();
            if(!$brand){
                throw new Exception('Brand not found');
            }
            $product = Product::create([
                'shop_id' => $shop->id,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
            ]);
            return $product;
    }
    public function create_sku($data , $product ,$sku_code){
            $sku = ProductSku::create([
                'product_id' => $product->id,
                'sku_code' => $sku_code,
                'price' => $data['price'],
                'discounted_price' => $data['discounted_price'] ?? 0,
                'stock_qty' => $data['stock_qty'],
                'attribute_values' => json_encode($data['attribute_values']),
            ]);
            return $sku;
    }
    public function load_product_relations($product){
            $product->load('attachment', 'skus');
            return $product;
    }
    public function update_product($data , $user){
        
            $shop = Shop::where('user_id', $user->id)->first();
            if(!$shop){
                throw new Exception('Shop not found');
            }
            if (isset($data['category_id'])) {
                $category = Category::where('id', $data['category_id'])->first();
                if(!$category){
                    throw new Exception('Category not found');
                }
            }
            if (isset($data['brand_id'])) {
                $brand = Brand::where('id', $data['brand_id'])->first();
                if(!$brand){
                    throw new Exception('Brand not found');
                }
            }
            $product = Product::where('id', $data['id'])->firstOrFail();
            if($product->shop_id != $shop->id){
                throw new Exception('Product does not belong to your shop.');
            }
            $product->update($data);
            return $product;
    }
    public function update_sku($data){
        if(isset($data['skus'])){
            foreach($data['skus'] as $sku){
                if(isset($sku['id'])){
                    $Exist_sku = ProductSku::where('id', $sku['id'])->firstOrFail();
                    $Exist_sku->update([
                        'price' => $sku['price'] ?? $Exist_sku->price,
                        'discounted_price' => $sku['discounted_price'] ?? $Exist_sku->discounted_price,
                        'stock_qty' => $sku['stock_qty'] ?? $Exist_sku->stock_qty,
                        'attribute_values' => isset($sku['attribute_values']) ? json_encode($sku['attribute_values']) : $Exist_sku->attribute_values,
                    ]);           
                }
            }
        }
    }
    public function delete_product($data , $user){
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        $product = Product::where('id', $data['id'])->firstOrFail();
        if($product->shop_id != $shop->id){
            throw new Exception('Product does not belong to your shop.');
        }
        $product->attachment()->delete();
        $product->skus()->delete();
        $product->delete();
        return $product;
    }
    public function my_product($user , $limit){
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        $products = Product::where('shop_id', $shop->id)->with('attachment', 'skus', 'category', 'brand')->paginate($limit);
        return $products;
    }
    public function products($limit , $request){
        $query = Product::with('attachment', 'skus', 'category', 'brand');
        if($request->has('filters')){
            DynamicFilter::applyNestedWhereHas($request, $query);
        }
        $products = $query->paginate($limit);
        return $products;
    }
    public function product_detail($slug){
        $product = Product::where('slug', $slug)->with('attachment', 'skus', 'category', 'brand','shop')->firstOrFail();
        return $product;
    }
    public function category_wise($data , $limit){
        $product = Product::where('category_id', $data['category_id'])->with('attachment', 'skus', 'category', 'brand')->paginate($limit);
        return $product;
    }
    public function shop_wise($data , $limit){
        $product = Product::where('shop_id', $data['shop_id'])->with('attachment', 'skus', 'category', 'brand')->paginate($limit);
        return $product;
    }
    public function brand_wise($data , $limit){
        $product = Product::where('brand_id', $data['brand_id'])->with('attachment', 'skus', 'category', 'shop', 'brand')->paginate($limit);
        return $product;
    }
}