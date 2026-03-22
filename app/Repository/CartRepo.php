<?php
namespace App\Repository;

use App\Models\Carts;

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
}