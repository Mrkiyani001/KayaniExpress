<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends BaseController
{
    public function create_category(Request $request)
    {
        $this->ValidateRequest($request, [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'commission_rate' => 'nullable|numeric|max:20',
            'sort_order' => 'nullable|integer',
        ]);
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
        $check_slug = Category::where('slug', Str::slug($request->name))->count();
        if($check_slug > 0){
            $slug = Str::slug($request->name) . '-' . ($check_slug + 1);
        }else{
            $slug = Str::slug($request->name);
        }
        $category = Category::create([
            'parent_id' => $request->parent_id ?? null,
            'name' => $request->name,
            'slug' => $slug,
            'icon' => $iconName ?? null,
            'image' => $imageName ?? null,
            'commission_rate' => $request->commission_rate ?? 0,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        return $this->Response(true, 'Category created successfully', $category, 200);
    }catch(Exception $e){
        return $this->Response(false, $e->getMessage(), [], 500);
    }  
}
public function update_category(Request $request){
    $this->ValidateRequest($request, [
        'id' => 'required|exists:categories,id',
        'name' => 'nullable|string|max:255',
        'parent_id' => 'nullable|exists:categories,id',
        'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        'commission_rate' => 'nullable|numeric|max:20',
        'sort_order' => 'nullable|integer',
    ]);
    try{
    $user = auth('api')->user();
    if(!$user){
        return $this->unauthorized();
    }
    if(!$user->hasRole(['Super Admin', 'Admin'])){
        return $this->NotAllowed();
    }
    $category = Category::where('id', $request->id)->first();
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
    if($request->name){
    $check_slug = Category::where('slug', Str::slug($request->name))->where('id', '!=', $request->id)->count();
    if($check_slug > 0){
        $slug = Str::slug($request->name) . '-' . ($check_slug + 1);

    }else{
        $slug = Str::slug($request->name);
    }
    $category->slug = $slug;
    }
    $category->update([
        'parent_id' => $request->parent_id ?? $category->parent_id,
        'name' => $request->name ?? $category->name,
        'icon' => $iconName ?? $category->icon,
        'image' => $imageName ?? $category->image,
        'commission_rate' => $request->commission_rate ?? $category->commission_rate,
        'sort_order' => $request->sort_order ?? $category->sort_order,
    ]);
    return $this->Response(true, 'Category updated successfully', $category, 200);
    }catch(Exception $e){
        return $this->Response(false, $e->getMessage(), [], 500);
    }  
}
 public function delete_category(Request $request){
        $this->ValidateRequest($request ,[ 
            'id'=> 'required|exists:categories,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $category = Category::findOrFail($request->id);
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
