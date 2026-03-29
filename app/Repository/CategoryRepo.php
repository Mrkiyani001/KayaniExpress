<?php
namespace App\Repository;

use App\Models\Category;

class CategoryRepo{
    public function create_category($data , $slug , $iconName , $imageName){
        
            $category = Category::create([
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'icon' => $iconName,
                'image' => $imageName,
                'commission_rate' => $data['commission_rate'] ?? 0,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
            return $category;
    }
    public function update_category($data){
        
            $category = Category::where('id', $data['id'])->firstOrFail();
            $category->update($data);
            return $category;
    }
    public function delete_category($data){
            $category = Category::where('id', $data['id'])->firstOrFail();
            Category::where('parent_id', $category->id)->delete(); // Delete Childrens
            $category->delete();
            return $category;
    }
    public function get_category($slug){
        $category = Category::where('slug', $slug)->with('children')->firstOrFail();
        return $category;
    }
    public function get_categories(){
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return $categories;
    }
}