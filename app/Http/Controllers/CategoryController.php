<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CreateRequest;
use App\Http\Requests\Category\DeleteRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends BaseController
{
    private $CategoryService;
    public function __construct(CategoryService $CategoryService){
        $this->CategoryService = $CategoryService;
    }
    public function create_category(CreateRequest $request)
    {
        $data = $request->validated();
        try{
            DB::beginTransaction();
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        $this->authorize('checkrole', Role::class);
        $category = $this->CategoryService->create_category($data , $request);
        DB::commit();
        return $this->Response(true, 'Category created successfully', $category, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, $e->getMessage(), [], 500);
    }  
}
public function update_category(UpdateRequest $request){
    $data = $request->validated();
    try{
    DB::beginTransaction();
    $user = Auth::user();
    if(!$user){
        return $this->unauthorized();
    }
    $this->authorize('checkrole', Role::class);
    $category = $this->CategoryService->update_category($data , $request);
    DB::commit();
    return $this->Response(true, 'Category updated successfully', $category, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, $e->getMessage(), [], 500);
    }  
}
 public function delete_category(DeleteRequest $request){
    $data = $request->validated();
    try{
        DB::beginTransaction();
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        $this->authorize('checkrole', Role::class);
        $category = $this->CategoryService->delete_category($data);
        DB::commit();
        return $this->Response(true, 'Category deleted successfully',[], 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, $e->getMessage(),[], 500);
    }
}
    public function get_category($slug){
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $category = $this->CategoryService->get_category($slug);
            return $this->Response(true, 'Category fetched successfully', $category, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function get_categories(){
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $categories = $this->CategoryService->get_categories();
            return $this->Response(true, 'Categories fetched successfully', $categories, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
}
