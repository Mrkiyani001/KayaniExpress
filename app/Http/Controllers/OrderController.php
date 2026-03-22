<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\DetailRequest;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Jobs\PlaceOrder;
use App\Models\Address;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService = $orderService;
    }
    public function PlaceOrder(PlaceOrderRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $order = $this->orderService->placeorder($user, $data);
            return $this->Response(true, 'Order placed successfully', $order, 201);
        }catch(Exception $e){
            return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
        }
    }
public function getOrderHistory(Request $request){
    try{
        $limit = (int) $request->input('limit', 10);
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $order = $this->orderService->getUserOrderHistory($user->id, $limit);
        $Data = $this->PaginateData($order, $order->items());
        return $this->Response(true, 'Order history fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function getOrderDetail(DetailRequest $request){
    try{
        $data = $request->validated();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        
        $order = $this->orderService->getOrderDetail($data, $user->id);
        return $this->Response(true, 'Order detail fetched successfully', $order, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong: '.$e->getMessage(), [], 500);
    }
}

public function CancelOrder(CancelOrderRequest $request){
    try{
        $data = $request->validated();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        
        $order = $this->orderService->cancelOrder($data, $user->id);
        return $this->Response(true, 'Order cancelled successfully', $order, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong: '.$e->getMessage(), [], 500);
    }
}
public function sellerorder(Request $request){
    try{
        $limit = (int) $request->input('limit', 10);
        $status = $request->input('status'); // Nullable filter parameter
        
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $order = $this->orderService->sellerorder($user->id, $limit, $status);
        $Data = $this->PaginateData($order, $order->items());
        return $this->Response(true, 'Seller order history fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
public function updateOrderStatus(UpdateStatusRequest $request){
    try{
        $data = $request->validated();
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $order = $this->orderService->updateOrderStatus($data, $user->id);
        return $this->Response(true, 'Order status updated successfully', $order, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong: '.$e->getMessage(), [], 500);
    }
}
public function getallorder(Request $request){
    try{
        $limit = (int) $request->input('limit', 10);
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole(['Super Admin','Admin'])){
            return $this->NotAllowed();
        }
        $order = $this->orderService->getallorder($limit);
        $Data = $this->PaginateData($order, $order->items());
        return $this->Response(true, 'Order history fetched successfully', $Data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Something went wrong'.$e->getMessage(),[], 500);
    }
}
}
