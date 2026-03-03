<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $table = 'product_skus';
    protected $fillable = [
        'product_id',
        'sku_code',
        'price',
        'discounted_price',
        'stock_qty',
        'attribute_values',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
