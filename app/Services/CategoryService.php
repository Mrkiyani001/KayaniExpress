<?php
namespace App\Services;
use App\Repository\CategoryRepo;
use App\Traits\UploadTraits;

use App\Models\Category;
use Exception;
use Illuminate\Support\Str;

class CategoryService{
    use UploadTraits;
    private $CategoryRepo;
    public function __construct(CategoryRepo $CategoryRepo){
        $this->CategoryRepo = $CategoryRepo;
    }
    public function create_category($data , $request){
        try{
            $iconName = null;
            $imageName = null;
            if($request->hasFile('icon')){
                $icon = $request->file('icon');
                $iconName = $this->upload($icon, 'categories/icons');
            }
            if($request->hasFile('image')){
                $image = $request->file('image');
                $imageName = $this->upload($image, 'categories/images');
            }
            $check_slug = Category::where('slug',Str::slug($data['name']))->count();
            if($check_slug > 0){
                $slug = Str::slug($data['name']) . '-' . ($check_slug + 1);
            }else{
                $slug = Str::slug($data['name']);
            }
            $category = $this->CategoryRepo->create_category($data , $slug , $iconName , $imageName);
            return $category;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function update_category($data , $request){
        try{
        if($request->hasFile('icon')){
        $icon = $request->file('icon');
        $data['icon'] = $this->upload($icon, 'categories/icons');
    }
    if($request->hasFile('image')){
        $image = $request->file('image');
        $data['image'] = $this->upload($image, 'categories/images');
    }
    if(!empty($data['name'])){
    $check_slug = Category::where('slug', Str::slug($data['name']))->where('id', '!=', $data['id'])->count();
    if($check_slug > 0){
        $data['slug'] = Str::slug($data['name']) . '-' . ($check_slug + 1);

    }else{
        $data['slug'] = Str::slug($data['name']);
    }    
   }
   $category = $this->CategoryRepo->update_category($data);
   return $category;
    }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_category($data){
        try{
            $category = $this->CategoryRepo->delete_category($data);
            return $category;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function get_category($slug){
        try{
            $category = $this->CategoryRepo->get_category($slug);
            return $category;
        }catch(Exception $e){
            throw $e;
        }
    }
    public function get_categories(){
        try{
            $categories = $this->CategoryRepo->get_categories();
            return $categories;
        }catch(Exception $e){
            throw $e;
        }
    }
}