<?php 
namespace App\Services;

use App\Models\Product;
use App\Models\ProductSku;
use App\Repository\ProductRepo;
use Exception;
use Illuminate\Support\Str;
use App\Traits\UploadTraits;
class ProductService{
    use UploadTraits;
    private $productRepo;
    public function __construct(ProductRepo $productRepo){
        $this->productRepo = $productRepo;
    }
    public function create_product($data , $user, $request){
        try{
            $check_slug = Product::where('slug', Str::slug($data['name']))->count();
            if($check_slug > 0){
                $slug = Str::slug($data['name']).'-'.($check_slug + 1);
            }else{
                $slug = Str::slug($data['name']);
            }
            
            $product= $this->productRepo->create_product($data , $user ,$slug);
            if(isset($data['skus']) && $product){
                foreach($data['skus'] as $sku){
                    $sku_code = Str::slug($data['name']).'-'.Str::slug(implode('-', $sku['attribute_values']));
                    $check_sku = ProductSku::where('sku_code', $sku_code)->count();
                    if($check_sku > 0){
                        $sku_code = $sku_code.'-'.($check_sku + 1);
                    }
                    $sku = $this->productRepo->create_sku($sku , $product ,$sku_code);
                }
            }
            if($request->hasFile('file')){
                $files = $request->file('file');
                foreach($files as $file){
                    $this->product_image($file, 'products', $product);
                }
                // Pehli image ko main mark karo (loop ke bahar)
                $product->attachment()->oldest()->update(['is_main' => true]);
            }
            if($product){
                $product = $this->productRepo->load_product_relations($product);
                return $product;
            }
            throw new Exception('Product not created');
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function update_product($data , $user , $request){
        if(isset($data['name'])){
            $check_slug = Product::where('slug', Str::slug($data['name']))->where('id', '!=', $data['id'])->count();
            if($check_slug > 0){
                $data['slug'] = Str::slug($data['name']).'-'.($check_slug + 1);
            }else{
                $data['slug'] = Str::slug($data['name']);
            }
        }
        $product = $this->productRepo->update_product($data , $user);
        $this->productRepo->update_sku($data);
        if($request->hasFile('file')){
                $files = $request->file('file');
                foreach($files as $file){
                    $this->product_image($file, 'products', $product);
                }
                // Pehli image ko main mark karo (loop ke bahar)
                $product->attachment()->oldest()->update(['is_main' => true]);
            }
        if($product){
            $this->productRepo->load_product_relations($product);
            return $product;
        }
        throw new Exception('Product not updated');
    }
    public function delete_product($data , $user){
        try{
            $product = $this->productRepo->delete_product($data , $user);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function my_product($user , $limit){
        try{
            $product = $this->productRepo->my_product($user , $limit);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function products($limit , $request){
        try{
            $product = $this->productRepo->products($limit , $request);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function product_detail($slug){
        try{
            $product = $this->productRepo->product_detail($slug);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function category_wise($data , $limit){
        try{
            $product = $this->productRepo->category_wise($data , $limit);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function shop_wise($data , $limit){
        try{
            $product = $this->productRepo->shop_wise($data , $limit);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function brand_wise($data , $limit){
        try{
            $product = $this->productRepo->brand_wise($data , $limit);
            return $product;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}
