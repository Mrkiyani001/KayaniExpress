<?php
namespace App\Services;

use App\Repository\SellerWalletRepo;

class SellerWalletService{
    protected $sellerWalletRepo;
    public function __construct(SellerWalletRepo $sellerWalletRepo){
        $this->sellerWalletRepo = $sellerWalletRepo;
    }
    public function balance($user){
        return $this->sellerWalletRepo->balance($user);
    }
    public function withdraw($data , $user){
        return $this->sellerWalletRepo->withdraw($data , $user);
    }
}