<?php

namespace App\Http\Controllers;

use App\Models\SellerWallet;
use App\Models\Shop;
use Exception;
use Illuminate\Http\Request;

class SellerWalletController extends BaseController
{
    public function balance(){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if($user->hasRole(['Super Admin'])){
                $wallet = SellerWallet::all();
                return $this->Response(true, 'Wallet balance', $wallet, 200);
            }
            $shop = Shop::where('user_id', $user->id)->first();
            if(!$shop){
                return $this->Response(false, 'Shop not found',[], 404);
            }
            $wallet = SellerWallet::where('shop_id', $shop->id)->first();
            if(!$wallet){
                return $this->Response(false, 'Wallet not found',[], 404);
            }
            return $this->Response(true, 'Wallet balance', $wallet, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(), [], 500);
        }
    }
    public function withdraw(Request $request){
        $this->ValidateRequest($request, [
            'amount' => 'required|numeric|min:1',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = Shop::where('user_id', $user->id)->first();
            if(!$shop){
                return $this->Response(false, 'Shop not found',[], 404);
            }
            $wallet = SellerWallet::where('shop_id', $shop->id)->first();
            if(!$wallet){
                return $this->Response(false, 'Wallet not found',[], 404);
            }
            if($wallet->withdrawable_balance < $request->amount){
                return $this->Response(false , 'Insufficient Balance. Please Recharge Your Wallet First', [], 400);
            }
            $wallet->withdrawable_balance -= $request->amount;
            $wallet->pending_balance += $request->amount;
            $wallet->save();
            return $this->Response(true, 'Withdrawal request sent successfully', $wallet, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(), [], 500);
        }
    }
    
}
