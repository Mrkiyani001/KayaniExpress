<?php
namespace App\Services;

use App\Repository\AddressRepo;

class AddressService{
    public function __construct(private AddressRepo $addressRepo){
    }
    public function createaddress($data , $user){
        $address = $this->addressRepo->createaddress($data , $user);
        return $address;
    }
    public function findAddress($addressId){
        $address = $this->addressRepo->findAddress($addressId);
        return $address;
    }
    public function updateaddress($data , $user){
        $address = $this->addressRepo->updateaddress($data , $user);
        return $address;
    }
    public function deleteaddress($data , $user){
        $address = $this->addressRepo->deleteaddress($data , $user);
        return $address;
    }
    public function getAddresses($user){
        $addresses = $this->addressRepo->getAddresses($user);
        return $addresses;
    }
    public function setDefault($data , $user){
        $address = $this->addressRepo->setDefault($data , $user);
        return $address;
    }
}