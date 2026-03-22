<?php
namespace App\Repository;

use App\Models\Address;

class AddressRepo{
    public function findAddress(array $data){
        $address = Address::where('id', $data['address_id'])->firstOrFail();
        return $address;
    }
}