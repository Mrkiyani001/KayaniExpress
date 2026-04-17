<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\add_cart_Request;
use App\Http\Requests\Cart\delete_cart_Request;
use App\Http\Requests\Cart\update_cart_Request;
use App\Models\Carts;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends BaseController
{
    private $cartService;
    public function __construct(CartService $cartService){
        $this->cartService = $cartService;
    }
    public function add_to_cart(add_cart_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = $this->cartService->addToCart($data , $user);
            DB::commit();
            return $this->Response(true, 'Product added to cart successfully', $cart, 201);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function get_cart(Request $request){
        try{
            $user =Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = $this->cartService->getCart($user);
            return $this->Response(true, 'Cart fetched successfully', $cart, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function update_cart(update_cart_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = $this->cartService->updatecart($data , $user);
            DB::commit();
            return $this->Response(true, 'Cart updated successfully', $cart, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function delete_cart(delete_cart_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = $this->cartService->deletecart($data , $user);
            DB::commit();
            return $this->Response(true, 'Cart deleted successfully', [], 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
}
