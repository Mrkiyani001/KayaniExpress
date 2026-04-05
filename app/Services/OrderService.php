<?php 
namespace App\Services;

use App\Jobs\PlaceOrder;
use App\Repository\OrderRepo;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService{
    private $orderRepo;
    public function __construct(OrderRepo $orderRepo){
        $this->orderRepo = $orderRepo;
    }
    public function placeorder($user, $data){
        try{
            $cart = $this->orderRepo->get_cart($user->id);
            $address = $this->orderRepo->get_address($data['address_id']);
            $shipping_cost = $address->area->delivery_charge ?? 0;
            $order = $this->orderRepo->createorder($user, $data, $shipping_cost);
            PlaceOrder::dispatch($order);
            return $order;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function processOrder($order ){
        try{
            DB::beginTransaction();
            $cart = $this->orderRepo->get_cart($order->user_id);
            $grand_total = 0;
            $total_discount_amount = 0;
            foreach($cart as $item){
                $sku = $item->product_sku; //Call from cart model
                if($sku->stock_qty < $item->qty){
                    throw new Exception('Stock is not available');
                }
                $discount_amount = 0;
                if($sku->discounted_price > 0){
                    $unit_price = $sku->price - $sku->discounted_price;
                    $discount_amount = $sku->discounted_price * $item->qty;
                }else{
                    $unit_price = $sku->price;
                }
                $total_price = $unit_price * $item->qty;
                $total_discount_amount += $discount_amount;
                $grand_total += $total_price;
                $admin_commission = $total_price * ($sku->product->category->commission_rate/100);
                $seller_payout = $total_price - $admin_commission;
                $order_items = $this->orderRepo->createorderitem(
                    $order->id, 
                    $sku->product->shop_id, 
                    $item->product_sku_id, 
                    $sku->product->name, 
                    $item->qty, 
                    $unit_price, 
                    $total_price,
                    $discount_amount, 
                    $admin_commission, 
                    $seller_payout, 
                    'pending'
                );
                $this->orderRepo->decrement_stock($item->product_sku_id, $item->qty);
            }
            $this->orderRepo->update_grand_total($order, $grand_total, $total_discount_amount);
            $this->orderRepo->create_order_status_history($order->id, 'pending', null, 'Order placed successfully', $order->user_id);
            $this->orderRepo->clear_cart($order->user_id);
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function CancelOrder($data, $user){
        try{
            DB::beginTransaction();
            $order = $this->orderRepo->cancel_order($data, $user);
            $this->orderRepo->create_order_status_history($order->id, 'cancelled', null, 'Order cancelled by user', $user->id);
            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function CancelStuckorder(){
        try{
            $stuckitems = $this->orderRepo->get_stuck_order(1);
            foreach($stuckitems as $item){
                $this->orderRepo->increment_stock($item->product_sku_id, $item->qty);
                $this->orderRepo->update_delivery_status($item->id, 'cancelled');
                $this->orderRepo->create_order_status_history($item->order->id, 'cancelled', $item->id, 'Order stuck', $item->order->user_id);
                $this->sync_order_status($item->order);
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function sync_order_status($order){
        try{
            $orderItems = $this->orderRepo->get_order($order);
            $item = $orderItems->items;
            $status = $item->pluck('delivery_status');
            $allpending = $status->contains('pending');
            $allconfirmed = $status->contains('confirmed');
            $allcancelled = $status->every(fn($status) => $status == 'cancelled');
            if($allpending){
                return;
            }
            if($allcancelled){
                $this->orderRepo->update_order_status($order->id, 'cancelled');
                $this->orderRepo->create_order_status_history($order->id, 'cancelled', null, 'Order cancelled by seller', $order->user_id);
            }
            if($allconfirmed){
                $this->orderRepo->update_order_status($order->id, 'confirmed');
                $this->orderRepo->create_order_status_history($order->id, 'confirmed', null, 'Order confirmed by seller', $order->user_id);
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_user_order_history($user, $limit){
        try{
            $order = $this->orderRepo->get_user_order_history($user, $limit);
            return $order;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_order_detail($data, $user){
        try{
            $order = $this->orderRepo->get_order_detail($data, $user);
            return $order;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_seller_order($user, $limit, $status){
        try{
            $order = $this->orderRepo->get_seller_order($user, $limit, $status);
            return $order;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function update_order_status($data, $user){
        try{
            $order = $this->orderRepo->update_order_status($data);
            $this->orderRepo->create_order_status_history($data['order_id'], $data['status'], $data['order_item_id'], $data['reason'], $user->id);
            $this->sync_order_status($order);
            return $order;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function getallorder($limit){
        try{
            $order = $this->orderRepo->getallorder($limit);
            return $order;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}