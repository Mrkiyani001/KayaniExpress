<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Exception;
use Illuminate\Http\Request;

class AreaController extends BaseController
{
    public function create(Request $request){
        $this->ValidateRequest($request,[
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'delivery_charge' => 'required|numeric',
            'status' => 'required|boolean',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::create([
                'name' => $request->name,
                'city_id' => $request->city_id,
                'delivery_charge' => $request->delivery_charge,
                'status' => $request->status,
            ]);
            return $this->Response(true, 'Area created successfully', $area, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area not created', $e->getMessage(), 500);
        }
    }
    public function update(Request $request){
        $this->ValidateRequest($request,[
            'id' => 'required|exists:areas,id',
            'name' => 'string|max:255',
            'city_id' => 'exists:cities,id',
            'delivery_charge' => 'numeric',
            'status' => 'boolean',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::findOrFail($request->id);
            $area->update($request->only([
                'name',
                'city_id',
                'delivery_charge',
                'status',
            ]));
            return $this->Response(true, 'Area updated successfully', $area, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area not updated', $e->getMessage(), 500);
        }
    }
    public function delete(Request $request){
        $this->ValidateRequest($request,[
            'id' => 'required|exists:areas,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::findOrFail($request->id);
            $area->delete();
            return $this->Response(true, 'Area deleted successfully', null, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area not deleted', $e->getMessage(), 500);
        }
    }
    public function city_wise_list(Request $request){
        $this->ValidateRequest($request,[
            'city_id' => 'required|exists:cities,id',
        ]);
        try{
            $limit = (int) $request->input('limit', 10);
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $area = Area::with('city')->where('city_id', $request->city_id)->paginate($limit);
            $data = $this->PaginateData($area, $area->items());
            return $this->Response(true, 'Area list', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area list not found', $e->getMessage(), 500);
        }
    }
    public function area_filter(Request $request){
        $this->ValidateRequest($request,[
            "status" => "required|boolean",
            "city_id" => "required|exists:cities,id",
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $limit = (int) $request->input('limit', 10);
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::with('city')
            ->where('city_id', $request->city_id)
            ->where('status', $request->status)
            ->paginate($limit);
            $data = $this->PaginateData($area, $area->items());
            return $this->Response(true, 'Area list', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area list not found', $e->getMessage(), 500);
        }
    }
}
