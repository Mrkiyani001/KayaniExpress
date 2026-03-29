<?php

namespace App\Http\Controllers;

use App\Http\Requests\WishList\Add_Request;
use App\Http\Requests\WishList\Delete_Request;
use App\Models\WishList;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\WishListService;

class WishListController extends BaseController
{
    private $wishlistService;
    public function __construct(WishListService $wishlistService){
        $this->wishlistService = $wishlistService;
    }
    public function add_to_wishlist(Add_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $wishlist = $this->wishlistService->add_to_wishlist($user, $data);
            DB::commit();
            $wishlist->load('product');
            return $this->Response(true, 'Product added to wishlist successfully', $wishlist, 201);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function get_wishlist(Request $request){
        try{
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $wishlist = $this->wishlistService->get_wishlist($user);
            return $this->Response(true, 'Wishlist fetched successfully', $wishlist, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function delete_wishlist(Delete_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $wishlist = $this->wishlistService->delete_wishlist($user, $data);
            DB::commit();
            return $this->Response(true, 'Wishlist deleted successfully', $wishlist, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
}
