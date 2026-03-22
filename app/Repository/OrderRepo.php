<?php
namespace App\Repository;

use App\Models\Carts;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductSku;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderRepo{
    public function getUserOrderHistory($userId, $limit = 10)
    {
        
            $orders = Order::with('statusHistory', 'items.product_sku.product')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($limit);
                
            return $orders;

    }
    public function getOrderDetail(array $data, $userId){
        
            $order = Order::with('statusHistory', 'items.product_sku.product')
                ->where('order_no', $data['order_no'])
                ->where('user_id', $userId) // SECURITY: Ensure order belongs to user
                ->first();
            return $order;
    }
    public function cancelorder(array $data, $userId){
        $order = Order::where('order_no', $data['order_no'])->where('user_id', $userId)->first();
        return $order; 
    }

    public function sellerorder($userId, $limit = 10, $status = null){
        $query = OrderItem::with('order', 'product_sku.product','shop','shop.user')
            ->whereHas('shop', function($query) use ($userId) {
                $query->where('user_id', $userId);
            });

        // Agar user ne status pass kiya hai (jaise ?status=pending)
        if ($status) {
            // Hum OrderItem ke "delivery_status" se ya phir "order_status" se filter laga sakte hain
            $query->whereHas('order', function($q) use ($status) {
                $q->where('order_status', $status);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($limit);
            
        return $orders;
    }
    public function updateOrderStatus(array $data, $userId){
        // Order -> items -> shop. Order directly doesn't have 'shop'
        $order = OrderItem::where('id', $data['order_item_id'])
            ->whereHas('shop', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->first();
        
        if(!$order){
            throw new Exception('Order not found or unauthorized');
        }

        return $order; 
    } 
    public function getallorder($limit){
        $order = Order::with('statusHistory', 'items.product_sku.product', 'items.shop', 'items.shop.user')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
        return $order;
    }
    public function createorder(array $data){
        $order = Order::create($data);
        return $order;
    }
    public function updateorder(array $data){
        $order = Order::update($data);
        return $order;
    }
    public function createorderitem(array $data){
        $orderitem = OrderItem::create($data);
        return $orderitem;
    }
    public function decrementstock(array $data){
        $productsku = ProductSku::where('id', $data['product_sku_id'])
        ->where('stock', '>=', $data['quantity'])
        ->decrement('stock', $data['quantity']);
        return $productsku;
    }
    public function createorderstatushistory(array $data){
        $orderstatus = OrderStatusHistory::create($data);
        return $orderstatus;
    }
    public function updateorderstatushistory(array $data){
        $orderstatus = OrderStatusHistory::create($data);
        return $orderstatus;
    }
}