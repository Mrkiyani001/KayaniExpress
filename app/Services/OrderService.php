<?php

namespace App\Services;

use App\Jobs\PlaceOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Repository\AddressRepo;
use App\Repository\CartRepo;
use App\Repository\OrderRepo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $orderRepo;
    protected $cartRepo;
    protected $addressRepo;
    public function __construct(OrderRepo $orderRepo, CartRepo $cartRepo, AddressRepo $addressRepo){
        $this->orderRepo = $orderRepo;
        $this->cartRepo = $cartRepo;
        $this->addressRepo = $addressRepo;
    }
    public function getUserOrderHistory($userId, $limit = 10)
    {
        try {
            $orders = $this->orderRepo->getUserOrderHistory($userId, $limit);
            return $orders;

        } catch (Exception $e) {
            Log::error('Error fetching order history: ' . $e->getMessage());
            throw new Exception('Failed to fetch order history');
        }
    }
    public function getOrderDetail(array $data, $userId){
        try{
            $order = $this->orderRepo->getOrderDetail($data, $userId);
            return $order;

        }catch(Exception $e){
            Log::error('Error fetching order detail: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function cancelOrder(array $data, $userId){
        try{
            $order = $this->orderRepo->cancelorder($data, $userId);
            if(!$order){
                throw new Exception('Order not found or unauthorized');
            }
            
            // `order_status` db column hai (`status` nahi)
            if(!in_array($order->order_status, ['pending','processing','confirmed'])){
                throw new Exception('Only pending, processing or confirmed orders can be cancelled');
            }   
            
            $order->order_status = 'cancelled';
            $order->save();

            // Status history zaruri hai!
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'cancelled',
                'note'       => 'Order cancelled by customer',
                'changed_by' => $userId,
            ]);
            Log::info('Order cancelled successfully');
            return $order; // Cancelled order wapis bhejo, history nahi

        }catch(Exception $e){
            Log::error('Error cancelling order: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
    public function sellerorder($userId, $limit = 10, $status = null){
        try{
            $orders = $this->orderRepo->sellerorder($userId, $limit, $status);
            return $orders;

        }catch(Exception $e){
            Log::error('Error fetching seller orders: ' . $e->getMessage());
            throw new Exception('Failed to fetch seller orders');
        }
    }
    public function updateOrderStatus(array $data, $userId){
        try{
            $order_item = $this->orderRepo->updateOrderStatus($data, $userId);
            $order_item->update([
                'delivery_status' => $data['delivery_status'],
            ]);
            OrderStatusHistory::create([
                'order_id'   => $order_item->id,
                'status'     => $data['delivery_status'],
                'note'       => 'Order item status updated by seller',
                'changed_by' => $userId,
            ]);
            return $order_item;

        }catch(Exception $e){
            Log::error('Error updating order status: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
    public function getallorder($limit){
        try{
            $orders = $this->orderRepo->getallorder($limit);
            return $orders;
        }catch(Exception $e){
            Log::error('Error fetching all orders: ' . $e->getMessage());
            throw new Exception('Failed to fetch all orders');
        }
    }
    public function placeorder($user, $data){
        try{
            $cart = $this->cartRepo->checkCart($user->id);
            if(!$cart){
                throw new Exception('Cart is empty');
            }
            $address = $this->addressRepo->findAddress($data['address_id']);
            if(!$address){
                throw new Exception('Address not found');
            }
            $shippingCost = $address->area->delivery_charge ?? 0;
            PlaceOrder::dispatch($user->id, $data['address_id'], $data['payment_method'], $shippingCost);
            return ['message' => 'Order is being processed'];

        }catch(Exception $e){
            Log::error('Error placing order: ' . $e->getMessage());
            throw new Exception('Failed to place order');
        }
    }
    public function processingorder($userId, $addressId, $payment_method, $shippingCost){
        try{
            DB::beginTransaction();
            $cart = $this->cartRepo->getbyUser($userId);
            if(!$cart || $cart->isEmpty()){
                throw new Exception('Cart is empty');
            }
            $address = $this->addressRepo->findAddress($addressId);
            if(!$address){
                throw new Exception('Address not found');
            }
            $order = $this->orderRepo->createorder([
                'order_no'       => 'ORD-' . time() . rand(1000, 9999),
                'user_id'        => $userId,
                'address_id'     => $address->id,
                'grand_total'    => 0,
                'shipping_cost'  => $shippingCost,
                'payment_method' => $payment_method,
            ]);
            if(!$order){
                DB::rollBack();
                throw new Exception('Failed to create order');
            }
            $this->orderRepo->createorderstatushistory([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'note'       => 'Order placed by customer',
                'changed_by' => $userId,
            ]);

            $grand_total = 0;
            foreach($cart as $items){
                $order_items = $items->product_sku;
                if($order_items->stock_qty < $items->qty){
                    DB::rollBack();
                    throw new Exception('Not enough stock for: ' . $order_items->product->name);
                }

            //Price Calution 
            $unit_price = 0;
            if($order_items->discounted_price > 0){
                $unit_price = $order_items->discounted_price;
            }else{
                $unit_price = $order_items->price;
            }
            $total_price = $unit_price * $items->qty;
            $grand_total += $total_price;
            $admin_commission = $total_price * ($order_items->product->category->commission_rate/100);
            $seller_payout = $total_price - $admin_commission;
             //Order Item Create
                OrderItem::create([
                    'order_id'         => $order->id,
                    'shop_id'          => $order_items->product->shop_id,
                    'product_sku_id'   => $order_items->id,
                    'product_name'     => $order_items->product->name,
                    'qty'              => $items->qty,
                    'unit_price'       => $unit_price,
                    'total_price'      => $total_price,
                    'admin_commission' => $admin_commission,
                    'seller_payout'    => $seller_payout,
                ]);

                // Decrement stock per item inside loop
                $this->orderRepo->decrementstock([
                    'product_sku_id' => $order_items->id,
                    'quantity'       => $items->qty,
                ]);
            }

            // Update grand_total after all items are processed
            $order->update([
                'grand_total'  => $grand_total + $shippingCost,
                'order_status' => 'processing',
            ]);

            $this->orderRepo->createorderstatushistory([
                'order_id'   => $order->id,
                'status'     => 'processing',
                'note'       => 'Order processed and inventory updated',
                'changed_by' => $userId,
            ]);

            $this->cartRepo->clearCart($userId);
            DB::commit();

            Log::info('Order processed successfully', ['order_id' => $order->id, 'user_id' => $userId]);
            return ['message' => 'Order created successfully'];

        }catch(Exception $e){
            DB::rollBack();
            Log::error('Error processing order: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}

