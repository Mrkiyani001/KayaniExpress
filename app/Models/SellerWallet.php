<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWallet extends Model
{
    protected $table = 'seller_wallets';
    protected $fillable = [
        'shop_id',
        'total_balance',
        'withdrawable_balance',
        'pending_balance'
    ];
    
    public function shop(){
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
