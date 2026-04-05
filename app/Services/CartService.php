<?php
namespace App\Services;

use App\Repository\CartRepo;
use Exception;

class CartService{
    public function __construct(private CartRepo $cartRepo){
    }
    public function addToCart($data , $user){
        try{
            $cart = $this->cartRepo->addToCart($data , $user);
            return $cart;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function getCart($user){
        try{
            $cart = $this->cartRepo->getCart($user);
            return $cart;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function updatecart($data , $user){
        try{
            $cart = $this->cartRepo->updatecart($data , $user);
            return $cart;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function deletecart($data , $user){
        try{
            $cart = $this->cartRepo->deletecart($data , $user);
            return $cart;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}