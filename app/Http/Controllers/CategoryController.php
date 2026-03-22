<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CreateRequest;
use App\Http\Requests\Category\DeleteRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends BaseController
{
    public function create_category(CreateRequest $request)
    {
        $data = $request->validated();
        try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin', 'Admin'])){
            return $this->NotAllowed();
        }
        if($request->hasFile('icon')){
            $icon = $request->file('icon');
            $iconName = $this->upload($icon, 'categories/icons');
        }
        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = $this->upload($image, 'categories/images');
        }
        $check_slug = Category::where('slug', Str::slug($data['name']))->count();
        if($check_slug > 0){
            $slug = Str::slug($data['name']) . '-' . ($check_slug + 1);
        }else{
            $slug = Str::slug($data['name']);
        }
        $category = Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $slug,
            'icon' => $iconName ?? null,
            'image' => $imageName ?? null,
            'commission_rate' => $data['commission_rate'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return $this->Response(true, 'Category created successfully', $category, 200);
    }catch(Exception $e){
        return $this->Response(false, $e->getMessage(), [], 500);
    }  
}
public function update_category(UpdateRequest $request){
    $data = $request->validated();
    try{
    $user = auth('api')->user();
    if(!$user){
        return $this->unauthorized();
    }
    if(!$user->hasRole(['Super Admin', 'Admin'])){
        return $this->NotAllowed();
    }
    $category = Category::where('id', $data['id'])->first();
    if(!$category){
        return $this->Response(false, 'Category not found', [], 404);
    }
    if($request->hasFile('icon')){
        $icon = $request->file('icon');
        $iconName = $this->upload($icon, 'categories/icons');
    }
    if($request->hasFile('image')){
        $image = $request->file('image');
        $imageName = $this->upload($image, 'categories/images');
    }
    if(!empty($data['name'])){
    $check_slug = Category::where('slug', Str::slug($data['name']))->where('id', '!=', $data['id'])->count();
    if($check_slug > 0){
        $slug = Str::slug($data['name']) . '-' . ($check_slug + 1);

    }else{
        $slug = Str::slug($data['name']);
    }
    $category->slug = $slug;
    }
    $category->update([
        'parent_id' => $data['parent_id'] ?? $category->parent_id,
        'name' => $data['name'] ?? $category->name,
        'icon' => $iconName ?? $category->icon,
        'image' => $imageName ?? $category->image,
        'commission_rate' => $data['commission_rate'] ?? $category->commission_rate,
        'sort_order' => $data['sort_order'] ?? $category->sort_order,
    ]);
    return $this->Response(true, 'Category updated successfully', $category, 200);
    }catch(Exception $e){
        return $this->Response(false, $e->getMessage(), [], 500);
    }  
}
 public function delete_category(DeleteRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $category = Category::findOrFail($data['id']);
            Category::where('parent_id', $category->id)->delete(); // Delete Childrens
            $category->delete();
            return $this->Response(true, 'Category deleted successfully',[], 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function get_category($slug){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $category = Category::where('slug', $slug)->with('children')->first();
            if(!$category){
                return $this->Response(false, 'Category not found',[], 404);
            }
            return $this->Response(true, 'Category fetched successfully', $category, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function get_categories(){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $categories = Category::whereNull('parent_id')->with('children')->get();
            return $this->Response(true, 'Categories fetched successfully', $categories, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
}
