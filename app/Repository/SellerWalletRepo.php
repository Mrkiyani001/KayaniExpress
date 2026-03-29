<?php
namespace App\Repository;

use App\Models\SellerWallet;
use App\Models\Shop;
use Exception;

class SellerWalletRepo{
    public function balance($user){
        
            if($user->hasRole(['Super Admin'])){
                $wallet = SellerWallet::all();
            }else{
                $shop = Shop::where('user_id', $user->id)->firstOrFail();
                $wallet = SellerWallet::where('shop_id', $shop->id)->firstOrFail();
            }
            return $wallet;
        
    }
    public function withdraw($data , $user){
        
            $shop = Shop::where('user_id', $user->id)->firstOrFail();
            $wallet = SellerWallet::where('shop_id', $shop->id)->firstOrFail();
            if($wallet->withdrawable_balance < $data['amount']){
                throw new Exception('Insufficient Balance. Please Recharge Your Wallet First');
            }
            $wallet->withdrawable_balance -= $data['amount'];
            $wallet->pending_balance += $data['amount'];
            $wallet->save();
            return $wallet;
        
    }
}