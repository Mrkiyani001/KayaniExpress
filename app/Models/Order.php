<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'order_no',
        'user_id',
        'address_id',
        'grand_total',
        'discount',
        'shipping_cost',
        'payment_method',
        'payment_status',
        'order_status',
        'coupon_id',
        'notes',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
