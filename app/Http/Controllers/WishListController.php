<?php

namespace App\Http\Controllers;

use App\Http\Requests\WishList\Add_Request;
use App\Http\Requests\WishList\Delete_Request;
use App\Models\WishList;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishListController extends BaseController
{
    public function add_to_wishlist(Add_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $wishlist = WishList::where('user_id', $user->id)->where('product_id', $data['product_id'])->first();
            if($wishlist){
                return $this->Response(false, 'Product already added to wishlist',[], 400);
            }
            $wishlist = WishList::create([
                'user_id' => $user->id,
                'product_id' => $data['product_id'],
            ]);
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
            $wishlist = WishList::where('user_id', $user->id)->with('product')->get();
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
            $wishlist = WishList::where('user_id', $user->id)->where('product_id', $data['product_id'])->first();
            if($wishlist){
                $wishlist->delete();
            }else{
                return $this->Response(false, 'Wishlist not found',[], 404);
            }
            DB::commit();
            return $this->Response(true, 'Wishlist deleted successfully', [], 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
}
