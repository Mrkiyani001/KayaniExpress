<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImages extends Model
{
    protected $table = 'products_images';
    protected $fillable = [
        'product_id',
        'file_name',
        'file_path',
        'file_type',
        'sort_order',
        'is_main',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
