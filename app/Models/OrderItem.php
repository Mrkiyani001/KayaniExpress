<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'shop_id',
        'product_sku_id',
        'product_name',
        'qty',
        'unit_price',
        'total_price',
        'admin_commission',
        'seller_payout',
        'delivery_status',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class,'shop_id');
    }
    public function product_sku()
    {
        return $this->belongsTo(ProductSku::class,'product_sku_id');
    }
}
