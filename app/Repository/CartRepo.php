<?php
namespace App\Repository;

use App\Models\Carts;
use Exception;

class CartRepo{
    public function getbyUser($userId){
        $cart = Carts::with('product_sku.product.category')
        ->where('user_id', $userId)
        ->get();
        return $cart;
    }
    public function checkCart($userId){
        $cart = Carts::where('user_id', $userId)->exists();
        return $cart;
    }
    public function clearCart($userId){
        $cart = Carts::where('user_id', $userId)->delete();
        return $cart;
    }
    public function addToCart($data , $user){
        $cart = Carts::where('user_id', $user->id)->where('product_sku_id', $data['product_sku_id'])->first();
        if($cart){
            $cart->qty += $data['qty'];
            $cart->save();
            $cart->load('product_sku');
            return $cart;
        }else{
            $cart = Carts::create([
                'user_id' => $user->id,
                'product_sku_id' => $data['product_sku_id'],
                'qty' => $data['qty'],
            ]);
            $cart->load('product_sku');
            return $cart;
        }
    }
    public function getCart($user){
        $cart = Carts::where('user_id', $user->id)->with('product_sku')->get();
        return $cart;
    }
    public function updatecart($data , $user){
        $cart = Carts::where('user_id', $user->id)->where('product_sku_id', $data['product_sku_id'])->first();
        if($cart){
            $cart->qty = $data['qty'];
            $cart->save();
            $cart->load('product_sku');
            return $cart;
        }else{
            throw new Exception('Cart not found');
        }
    }
    public function deletecart($data , $user){
        $cart = Carts::where('user_id', $user->id)->where('product_sku_id', $data['product_sku_id'])->first();
        if($cart){
            $cart->delete();
            return $cart;
        }else{
            throw new Exception('Cart not found');
        }
    }
}