<?php

namespace App\Repository;

use App\Models\Coupon;
use Exception;
use Illuminate\Support\Str;

class CouponRepo
{
    public function create_coupon($data, $shop_id)
    {
        $coupon = Coupon::create([
            'code' => strtoupper(Str::random(8)),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order' => $data['min_order'] ?? null,
            'max_discount' => $data['max_discount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'shop_id' => $shop_id,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);
        if($coupon){
            return $coupon;
        }
        throw new Exception('Coupon not created');
    }

    public function update_coupon($data)
    {
        $coupon = Coupon::where('id', $data['id'])->firstOrFail();
        $coupon->update($data);
        return $coupon;
    }

    public function delete_coupon($data)
    {
        $coupon = Coupon::where('id', $data['id'])->firstOrFail();
        $coupon->delete();
        return $coupon;
    }

    public function get_all_coupons()
    {
        return Coupon::all();
    }
    public function apply_coupon($data)
    {
        $coupon = $this->validate_coupon($data['code'], $data['order_amount']);
        return $coupon;
    }

    // ─── Shared core methods (used by both CouponService & OrderService) ──────

    public function validate_coupon($code, $order_amount = null)
    {
        $coupon = Coupon::where('code', $code)->firstOrFail();

        if ($coupon->status !== 'active') {
            throw new Exception('Coupon is not active');
        }
        if ($coupon->expires_at !== null && $coupon->expires_at < now()) {
            throw new Exception('Coupon has expired');
        }
        if ($coupon->starts_at !== null && $coupon->starts_at > now()) {
            throw new Exception('Coupon is not valid yet');
        }
        if ($coupon->usage_limit !== null && $coupon->usage_limit <= $coupon->used_count) {
            throw new Exception('Coupon has reached its usage limit');
        }
        if ($order_amount !== null && $coupon->min_order !== null && $coupon->min_order > $order_amount) {
            throw new Exception('Minimum order amount not met');
        }

        return $coupon;
    }

    public function calculate_discount($coupon, $order_amount)
    {
        if ($coupon->type === 'fixed') {
            $discount = $coupon->value;
        } else {
            $discount = ($coupon->value / 100) * $order_amount;
            if ($coupon->max_discount !== null && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        }

        // Discount can never exceed the total payable
        return min($discount, $order_amount);
    }
}