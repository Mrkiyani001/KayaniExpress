<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'shop_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'status',
        'is_featured',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function attachment()
    {
        return $this->hasMany(ProductImages::class);
    }
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }
}
