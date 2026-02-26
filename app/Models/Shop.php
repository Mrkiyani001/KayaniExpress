<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table = 'shops';
    protected $fillable = [
        'user_id',
        'shop_name',
        'slug',
        'logo',
        'banner',
        'description',
        'phone',
        'city_id',
        'status',
        'verified_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
   public function city()
   {
       return $this->belongsTo(City::class, 'city_id');
   }
   public function wallet()
   {
       return $this->hasOne(SellerWallet::class);
   }
}
