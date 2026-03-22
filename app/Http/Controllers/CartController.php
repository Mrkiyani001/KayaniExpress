<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\add_cart_Request;
use App\Http\Requests\Cart\delete_cart_Request;
use App\Http\Requests\Cart\update_cart_Request;
use App\Models\Carts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends BaseController
{
    public function add_to_cart(add_cart_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = Carts::where('user_id', $user->id)->where('product_sku_id', $data['product_sku_id'])->first();
            if($cart){
                $cart->qty += $data['qty'];
                $cart->save();
            }else{
                $cart = Carts::create([
                    'user_id' => $user->id,
                    'product_sku_id' => $data['product_sku_id'],
                    'qty' => $data['qty'],
                ]);
            }
            DB::commit();
            $cart->load('product_sku');
            return $this->Response(true, 'Product added to cart successfully', $cart, 201);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function get_cart(Request $request){
        try{
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = Carts::where('user_id', $user->id)->with('product_sku')->get();
            return $this->Response(true, 'Cart fetched successfully', $cart, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
    public function update_cart(update_cart_Request $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = Carts::where('user_id', $user->id)->where('product_sku_id', $data['product_sku_id'])->first();
            if($cart){
                $cart->qty = $data['qty'];
                $cart->save();
            }else{
                return $this->Response(false, 'Cart not found',[], 404);
            }
            DB::commit();
            $cart->load('product_sku');
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
            $user =auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $cart = Carts::where('user_id', $user->id)->where('product_sku_id', $data['product_sku_id'])->first();
            if($cart){
                $cart->delete();
            }else{
                return $this->Response(false, 'Cart not found',[], 404);
            }
            DB::commit();
            return $this->Response(true, 'Cart deleted successfully', [], 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
}
