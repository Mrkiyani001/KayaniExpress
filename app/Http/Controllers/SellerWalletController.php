<?php

namespace App\Http\Controllers;

use App\Http\Requests\SellerWallet\WithdrawRequest;
use App\Models\SellerWallet;
use App\Models\Shop;
use App\Services\SellerWalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerWalletController extends BaseController
{
    private $sellerWalletService;
    public function __construct(SellerWalletService $sellerWalletService){
        $this->sellerWalletService = $sellerWalletService;
    }
    public function balance(){
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $wallet = $this->sellerWalletService->balance($user);
            return $this->Response(true, 'Wallet balance', $wallet, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(), [], 500);
        }
    }
    public function withdraw(WithdrawRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $wallet = $this->sellerWalletService->withdraw($data , $user);
            DB::commit();
            return $this->Response(true, 'Withdrawal request sent successfully', $wallet, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(), [], 500);
        }
    }
    
}
