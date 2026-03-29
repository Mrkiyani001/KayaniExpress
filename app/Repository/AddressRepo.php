<?php
namespace App\Repository;

use App\Models\Address;
use App\Models\Area;
use Exception;

class AddressRepo{

    public function createaddress($data , $user){
        $area = Area::where('id', $data['area_id'])->where('city_id', $data['city_id'])->first();
            if(!$area){
               throw new Exception('Area not found in the given city');
            }
            $address = Address::create([
                'user_id'=>$user->id,
                'full_name'=>$data['full_name'],
                'type'=>$data['type'],
                'city_id'=>$data['city_id'],
                'area_id'=>$data['area_id'],
                'phone'=>$data['phone'],
                'address_line'=>$data['address_line'],
                'is_default'=>$data['is_default'],
            ]);
            return $address;
    }
    public function findAddress($addressId ){
        $address = Address::where('id', $addressId)->firstOrFail();
        return $address;
    }
    public function updateaddress($data , $user){
        $address = $this->findAddress($data['id'] , $user);
        if(isset($data['area_id']) || isset($data['city_id'])){
        $areaId = $data['area_id'] ?? $address->area_id;
        $cityId = $data['city_id'] ?? $address->city_id;
        $area = Area::where('id', $areaId)->where('city_id', $cityId)->first();
        if(!$area){
            throw new Exception('Area not found in the given city');
        }
    }
        $address->update($data);
        return $address;
    }
    public function deleteaddress($data , $user){
        $address = $this->findAddress($data['id']);
        if($address->user_id != $user->id){
            throw new Exception('You are not authorized to delete this address');
        }
        $address->delete();
        return $address;
    }
    public function getAddresses($user){
        if($user->hasRole(['Super Admin','Admin'])){
            $addresses = Address::with('user','city','area')->get();
        }else{
            $addresses = Address::where('user_id', $user->id)->with('user','city','area')->get();
        }
        return $addresses;
    }
    public function setDefault($data , $user){
        Address::where('user_id', $user->id)->update([
            'is_default'=>false,
        ]);
        Address::where('id', $data['id'])->where('user_id', $user->id)->update([
            'is_default'=>true,
        ]);
        $address = Address::where('id', $data['id'])
        ->where('user_id', $user->id)
        ->with('user','city','area')
        ->first();
        return $address;
    }
}