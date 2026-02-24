<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Area;
use Exception;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function create(Request $request){
        $this->ValidateRequest($request,[
            'full_name'=>'required|string|max:255',
            'type'=>'required|in:home,work,other',
            'city_id'=>'required|exists:cities,id',
            'area_id'=>'required|exists:areas,id',
            'phone'=>'required|string|max:255',
            'address_line'=>'required|string|max:255',
            'is_default'=>'required|boolean',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $area = Area::where('id', $request->area_id)->where('city_id', $request->city_id)->first();
            if(!$area){
                return $this->Response(false, 'Area not found in the given city', null,404);
            }
            $address = Address::create([
                'user_id'=>$user->id,
                'full_name'=>$request->full_name,
                'type'=>$request->type,
                'city_id'=>$request->city_id,
                'area_id'=>$request->area_id,
                'phone'=>$request->phone,
                'address_line'=>$request->address_line,
                'is_default'=>$request->is_default,
            ]);
            return $this->Response(true, 'Address created successfully', $address,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to create address', null,500);
        }
    }

    public function update(Request $request){
        $this->ValidateRequest($request,[
            'id'=>'exists:addresses,id',
            'full_name'=>'string|max:255',
            'type'=>'in:home,work,other',
            'city_id'=>'exists:cities,id',
            'area_id'=>'exists:areas,id',
            'phone'=>'string|max:255',
            'address_line'=>'string|max:255',
            'is_default'=>'boolean',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = Address::where('id', $request->id)->first();
            if(!$address){
                return $this->Response(false, 'Address not found', null,404);
            }
            if($request->area_id || $request->city_id){
                $cityId = $request->city_id ?? $address->city_id;
                $areaId = $request->area_id ?? $address->area_id;
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

    public function delete(Request $request){
        $this->ValidateRequest($request,[
            'id'=>'required|exists:addresses,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = Address::where('id', $request->id)->where('user_id', $user->id)->first();
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
    public function setDefault(Request $request){
        $this->ValidateRequest($request,[
            'id'=>'required|exists:addresses,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            Address::where('user_id', $user->id)->update([
                'is_default'=>false,
            ]);
            Address::where('id', $request->id)->where('user_id', $user->id)->update([
                'is_default'=>true,
            ]);
            $address = Address::where('id', $request->id)
            ->where('user_id', $user->id)
            ->with('user','city','area')
            ->first();
            return $this->Response(true, 'Address set as default successfully', $address,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to set address as default', null,500);
        }
    }
}
