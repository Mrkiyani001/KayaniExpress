<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Carts;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
    public function index(Request $request){
        $this->ValidateRequest($request,[
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod,online',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = Address::where('id', $request->address_id)->first();
            if(!$address){
                return $this->Response(false, 'Address not found',[], 404);
            }
            $shipping_cost = $address->area->delivery_charge ?? 0;
            $cart= Carts::where('user_id', $user->id)->get();
            if($cart->isEmpty()){
                return $this->Response(false, 'Cart is empty',[], 404);
            }
            $grand_total = 0;
            $order = Order::create([
                'order_no' => 'ORD-'. time().rand(1000,9999),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'grand_total' => $grand_total,
                'shipping_cost' => $shipping_cost,
                'payment_method' => $request->payment_method,
            ]);
            foreach($cart as $item){
                $sku = $item->product_sku;   // call relationship from cart model
                if($sku->stock_qty < $item->qty){
                    return $this->Response(false, 'Not enough stock',[], 404);
                }
                //price calculation
                $unit_price = $sku->discounted_price > 0 ? $sku->discounted_price : $sku->price;
                $total_price = $unit_price * $item->qty;
                $grand_total += $total_price;
                $admin_commission = $total_price * ($sku->product->category->commission_rate/100);
                $seller_payout = $total_price - $admin_commission;
                // Orderlist
                $order_list = OrderItem::create([
                    'order_id' => $order->id,
                    'shop_id' => $sku->product->shop_id,
                    'product_sku_id' => $sku->id,
                    'product_name' => $sku->product->name,
                    'qty' => $item->qty,
                    'unit_price' => $unit_price,
                    'total_price' => $total_price,
                    'admin_commission' => $admin_commission,
                    'seller_payout' => $seller_payout,
                ]);
                $sku->stock_qty -= $item->qty;
                $sku->save();
                
            }
            $order->update([
                'grand_total' => $grand_total + $shipping_cost,
            ]);
            Carts::where('user_id', $user->id)->delete();
            DB::commit();
            return $this->Response(true, 'Order placed successfully', $order, 201);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
}
