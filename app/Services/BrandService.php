<?php
namespace App\Services;

use App\Models\Brand;
use App\Repository\BrandRepo;
use Exception;
use App\Traits\UploadTraits;
use Illuminate\Support\Str;

class BrandService{
    use UploadTraits;
    private $BrandRepo;
    public function __construct(BrandRepo $BrandRepo){
        $this->BrandRepo = $BrandRepo;
    }
    public function create_brand($data , $request){
        try{
            $logo_path = null;
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
            $brand = $this->BrandRepo->create_brand($data , $logo_path , $slug);
            return $brand;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function update_brand($data , $request){
        try{
            if($request->hasFile('logo')){
                $logo = $request->file('logo');
                $data['logo'] = $this->upload($logo , 'Brands/Logos');
            }
            if(!empty($data['name'])){
                $check_slug = Brand::where('slug', Str::slug($data['name']))->where('id', '!=', $data['brand_id'])->count();
                if($check_slug > 0){
                    $data['slug'] = Str::slug($data['name']) . '-' . ($check_slug + 1);
                }else{
                    $data['slug'] = Str::slug($data['name']);
                }
            }
            
            $brand = $this->BrandRepo->update_brand($data);
            return $brand;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_brand($data){
        try{
            $brand = $this->BrandRepo->delete_brand($data);
            return $brand;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_all_brands($user){
        try{
            $brands = $this->BrandRepo->get_all_brands($user);
            return $brands;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function get_brand($slug){
        try{
            $brand = $this->BrandRepo->get_brand($slug);
            return $brand;
        }catch(Exception $e){
            throw $e;
        }
    }
}