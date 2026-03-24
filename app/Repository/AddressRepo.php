<?php
namespace App\Repository;

use App\Models\Address;

class AddressRepo{
    public function findAddress($addressId){
        $address = Address::where('id', $addressId)->firstOrFail();
        return $address;
    }
}