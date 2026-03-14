<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    protected $table = 'carts';
    protected $fillable = [
        'user_id',
        'product_sku_id',
        'qty',
    ];
    public function product_sku()
    {
        return $this->belongsTo(ProductSku::class,'product_sku_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
