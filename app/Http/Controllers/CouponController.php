<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponsRequest\ApplyRequest;
use App\Http\Requests\CouponsRequest\CreateRequest;
use App\Http\Requests\CouponsRequest\DeleteRequest;
use App\Http\Requests\CouponsRequest\UpdateRequest;
use App\Services\CouponsService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponController extends BaseController
{
    private $couponService;
    public function __construct(CouponsService $couponService)
    {
        $this->couponService = $couponService;
    }
    public function create_coupon(CreateRequest $request)
    {
        try{
            DB::beginTransaction();
        $data = $request->validated();
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        if($user->hasRole(['Admin', 'Super Admin'])){
            $shop_id = null;
        }else{
            $shop_id = $user->shop ? $user->shop->id : null;
        }
        $coupon = $this->couponService->create_coupon($data, $shop_id);
        DB::commit();
        return $this->Response(true, 'Coupon created successfully', $coupon, 201);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Coupon not created: '.$e->getMessage(), null, 500);
    }
    }
    public function update_coupon(UpdateRequest $request)
    {
    try{
        DB::beginTransaction();
        $data = $request->validated();
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        $coupon = $this->couponService->update_coupon($data);
        DB::commit();
        return $this->Response(true, 'Coupon updated successfully', $coupon, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'Coupon not updated: '.$e->getMessage(), null, 500);
    }
    }
    public function delete_coupon(DeleteRequest $request)
    {
        try{
            DB::beginTransaction();
            $data = $request->validated();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $coupon = $this->couponService->delete_coupon($data);
            DB::commit();
            return $this->Response(true, 'Coupon deleted successfully', $coupon, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Coupon not deleted: '.$e->getMessage(), null, 500);
        }
    }
    public function get_all_coupons()
    {
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $coupons = $this->couponService->get_all_coupons();
            return $this->Response(true, 'Coupons fetched successfully', $coupons, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Coupons not fetched: '.$e->getMessage(), null, 500);
        }
    }
    public function apply_coupon(ApplyRequest $request)
    {
        try {
            $data = $request->validated();
            $user = Auth::user();
            if (!$user) {
                return $this->unauthorized();
            }
            $result = $this->couponService->apply_coupon($data);
            return $this->Response(true, 'Coupon applied successfully', $result, 200);
        } catch (Exception $e) {
            return $this->Response(false, 'Coupon not applied: '.$e->getMessage(), null, 400);
        }
    }
}
