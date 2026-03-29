<?php
namespace App\Repository;

use App\Models\WishList;
use Exception;

class WishListRepo{
    public function add_to_wishlist($user, $data){
       $wishlist = WishList::where('user_id', $user->id)->where('product_id', $data['product_id'])->first();
       if($wishlist){
           return $wishlist;
       }
            $wishlist = WishList::create([
                'user_id' => $user->id,
                'product_id' => $data['product_id'],
            ]);
            return $wishlist;
        
    }
    public function get_wishlist($user){
        try{
            $wishlist = WishList::where('user_id', $user->id)->with('product')->get();
            return $wishlist;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_wishlist($user, $data){
        try{
            $wishlist = WishList::where('user_id', $user->id)->where('product_id', $data['product_id'])->first();
            if(!$wishlist){
                throw new Exception('Wishlist not found');
            }
            $wishlist->delete();
            return $wishlist;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}