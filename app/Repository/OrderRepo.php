<?php 
namespace App\Repository;

use App\Models\Address;
use App\Models\Carts;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductSku;
use App\Events\PublishRabbitMQEvent;
use Exception;

class OrderRepo{

    public function get_cart($user_id){
        $cart = Carts::where('user_id', $user_id)->get();
        if($cart->isEmpty()){
            throw new Exception('Cart is empty');
        }
        return $cart;
    }
    public function get_address($address_id){
        $address = Address::where('id', $address_id)->firstOrFail();
        return $address;
    }
    public function createorder($user, $data, $shipping_cost, $coupon_id = null){
        $order = Order::create(['order_no' => 'ORD-'. time().rand(1000,9999),
                'user_id' => $user->id,
                'address_id' => $data['address_id'],
                'grand_total' => 0,
                'shipping_cost' => $shipping_cost,
                'payment_method' => $data['payment_method'],
                'coupon_id' => $coupon_id,
        ]);
        return $order;
    }
    public function createorderitem($order_id, $shop_id, $product_sku_id, $product_name, $qty, $unit_price, $total_price, $discount_amount, $admin_commission, $seller_payout, $delivery_status){
        $order_items = OrderItem::create([
            'order_id' => $order_id,
            'shop_id' => $shop_id,
            'product_sku_id' => $product_sku_id,
            'product_name' => $product_name,
            'qty' => $qty,
            'unit_price' => $unit_price,
            'total_price' => $total_price,
            'discount_amount' => $discount_amount,
            'admin_commission' => $admin_commission,
            'seller_payout' => $seller_payout,
            'delivery_status' => $delivery_status,
        ]);
        return $order_items;
    }
    public function decrement_stock($product_sku_id, $qty){
        $product_sku = ProductSku::where('id', $product_sku_id)->firstOrFail();
        $product_sku->update([
            'stock_qty' => $product_sku->stock_qty - $qty,
        ]);
    }
    public function update_grand_total($order, $grand_total, $total_discount_amount, $coupon_discount = 0){
        $order->update([
            'grand_total' => $grand_total + $order->shipping_cost - $coupon_discount,
            'discount' => $total_discount_amount + $coupon_discount,
        ]);
    }
    public function apply_coupon_usage($coupon_id){
        $coupon = Coupon::where('id', $coupon_id)->firstOrFail();
        $coupon->update([
            'used_count' => $coupon->used_count + 1,
        ]);
    }
    public function create_order_status_history($order_id,$status, $order_item_id = null, $note = null, $changed_by = null){
        $order_status_history = OrderStatusHistory::create([
            'order_id' => $order_id,
            'order_item_id' => $order_item_id,
            'status' => $status,
            'note' => $note,
            'changed_by' => $changed_by,
        ]);
        return $order_status_history;
    }
    public function clear_cart($user_id){
        $cart = Carts::where('user_id', $user_id)->delete();
        return $cart;
    }
    public function cancel_order($data, $user){
        $order = Order::where('id', $data['order_id'])->where('user_id', $user->id)->first();
        if(!in_array($order->order_status, ['pending', 'confirmed', 'processing'])){
            throw new Exception('Order is not in pending, confirmed or processing state');
        }
        $order->update([
            'order_status' => 'cancelled',
        ]);
        $data = [
            'event'    => 'order.cancelled',
            'order_id' => $order->id,
            'user_id'  => $order->user_id,
            'order_no' => $order->order_no,
            'amount'   => $order->grand_total,
            'timestamp'=> now()->toISOString(),
        ];
        PublishRabbitMQEvent::dispatch('order.cancelled', 'order.cancelled', $data);
        return $order;
    }
    public function get_stuck_order(int $minutes){
        $orders = OrderItem::with('order')
        ->where('delivery_status', 'pending')
        ->where('created_at', '<', now()->subMinutes($minutes))->get();
        return $orders;
    }
    public function increment_stock($product_sku_id, $qty){
        $product_sku = ProductSku::where('id', $product_sku_id)->firstOrFail();
        $product_sku->update([
            'stock_qty' => $product_sku->stock_qty + $qty,
        ]);
    }
    public function update_delivery_status($order_item_id, $status){
        $order_item = OrderItem::where('id', $order_item_id)->firstOrFail();
        $order_item->update([
            'delivery_status' => $status,
        ]);
        $data = [
            'event'    => 'order.' . $status,
            'order_id' => $order_item->order_id,
            'user_id'  => $order_item->order->user_id,
            'order_no' => $order_item->order->order_no,
            'amount'   => $order_item->order->grand_total,
            'timestamp'=> now()->toISOString(),
        ];
        PublishRabbitMQEvent::dispatch('order.' . $status, 'order.' . $status, $data);
    }
    public function get_order($order){
        $orderItems = Order::with('items')->where('id', $order->id)->firstOrFail();
        return $orderItems;
    }
    public function update_order_status($data){
        $order = Order::where('id', $data['order_id'])->firstOrFail();
        $order->update([
            'order_status' => $data['status'],
        ]);
        return $order;
    }
    public function get_user_order_history($user, $limit){
        $order = Order::with(['statusHistory' , 'items.product_sku.product'])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->paginate($limit);
        return $order;
    }
    public function get_order_detail($data, $user){
        $order = Order::with(['statusHistory' , 'items.product_sku.product'])
        ->where('id', $data['order_id'])
        ->where('user_id', $user->id)
        ->firstOrFail();
        return $order;
    }
    public function get_seller_order($user, $limit, $status = null){
        $query = OrderItem::with(['order', 'product_sku.product','shop','shop.user'])
        ->whereRelation('shop', 'user_id', $user->id);
        if($status){
            $query->whereRelation('order', 'order_status', $status);
        }
        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
    public function getallorder($limit){
        $order = Order::with('statusHistory', 'items.product_sku.product', 'items.shop', 'items.shop.user')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
        return $order;
    }
}