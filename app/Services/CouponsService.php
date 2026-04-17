<?php

namespace App\Services;

use App\Repository\CouponRepo;
use Exception;

class CouponsService
{
    private $couponRepo;
    public function __construct(CouponRepo $couponRepo)
    {
        $this->couponRepo = $couponRepo;
    }

    public function create_coupon($data, $shop_id)
    {
        try {
            $coupon = $this->couponRepo->create_coupon($data, $shop_id);
            return $coupon;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update_coupon($data)
    {
        try {
            $coupon = $this->couponRepo->update_coupon($data);
            return $coupon;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete_coupon($data)
    {
        try {
            $coupon = $this->couponRepo->delete_coupon($data);
            return $coupon;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function get_all_coupons()
    {
        try {
            $coupons = $this->couponRepo->get_all_coupons();
            return $coupons;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function apply_coupon($data)
    {
        try {
            // Repo: validate karo
            $coupon = $this->couponRepo->validate_coupon($data['code'], $data['order_amount']);
            
            // Repo: calculate karo (ek hi jagah logic)
            $discount_amount = $this->couponRepo->calculate_discount($coupon, $data['order_amount']);

            return [
                'coupon'          => $coupon,
                'order_amount'    => $data['order_amount'],
                'discount_amount' => $discount_amount,
                'payable_amount'  => $data['order_amount'] - $discount_amount
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}