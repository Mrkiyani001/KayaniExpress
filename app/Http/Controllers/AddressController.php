<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequests\CreateRequest;
use App\Http\Requests\AddressRequests\DeleteRequest;
use App\Http\Requests\AddressRequests\SetRequest;
use App\Http\Requests\AddressRequests\UpdateRequest;
use App\Models\Address;
use App\Models\Area;
use Exception;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function create(CreateRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $area = Area::where('id', $data['area_id'])->where('city_id', $data['city_id'])->first();
            if(!$area){
                return $this->Response(false, 'Area not found in the given city', null,404);
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
            return $this->Response(true, 'Address created successfully', $address,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to create address', null,500);
        }
    }

    public function update(UpdateRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = Address::where('id', $data['id'])->first();
            if(!$address){
                return $this->Response(false, 'Address not found', null,404);
            }
            if(isset($data['area_id']) || isset($data['city_id'])){
                $cityId = $data['city_id'] ?? $address->city_id;
                $areaId = $data['area_id'] ?? $address->area_id;
                $area = Area::where('id', $areaId)->where('city_id', $cityId)->first();
                if(!$area){
                    return $this->Response(false, 'Area not found in the given city', null,404);
                }
            }
            $address->update($request->only([
                'full_name',
                'type',
                'city_id',
                'area_id',
                'phone',
                'address_line',
                'is_default',
            ]));
            return $this->Response(true, 'Address updated successfully', $address,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to update address', null,500);
        }
    }

    public function delete(DeleteRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = Address::where('id', $data['id'])->where('user_id', $user->id)->first();
            if(!$address){
                return $this->Response(false, 'Address not found', null,404);
            }
            $address->delete();
            return $this->Response(true, 'Address deleted successfully', null,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to delete address', null,500);
        }
    }

    public function list(Request $request){
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if($user->hasRole(['Super Admin','Admin'])){
                $addresses = Address::with('user','city','area')->get();
            }else{
                $addresses = Address::where('user_id', $user->id)->with('user','city','area')->get();
            }
            return $this->Response(true, 'Addresses fetched successfully', $addresses,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to fetch addresses', null,500);
        }
    }
    public function setDefault(SetRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
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
            return $this->Response(true, 'Address set as default successfully', $address,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to set address as default', null,500);
        }
    }
}
