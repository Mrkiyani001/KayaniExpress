<?php 
namespace App\Services;
use App\Repository\WishListRepo;
use Exception;

class WishListService{
    private $wishlistRepo;
    public function __construct(WishListRepo $wishlistRepo){
        $this->wishlistRepo = $wishlistRepo;
    }
    public function add_to_wishlist($user, $data){
        try{
            $wishlist = $this->wishlistRepo->add_to_wishlist($user, $data);
            return $wishlist;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_wishlist($user){
        try{
            $wishlist = $this->wishlistRepo->get_wishlist($user);
            return $wishlist;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_wishlist($user, $data){
        try{
            $wishlist = $this->wishlistRepo->delete_wishlist($user, $data);
            return $wishlist;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}