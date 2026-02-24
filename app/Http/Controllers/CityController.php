<?php

namespace App\Http\Controllers;

use App\Models\City;
use Exception;
use Illuminate\Http\Request;

class CityController extends BaseController
{
    public function create(Request $request){
        $this->ValidateRequest($request, [
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole('Super Admin')){
                return $this->NotAllowed();
            }
            $city = City::where('name', $request->name)->first();
            if($city){
                return $this->Response(false, 'City already exists', null, 400);
            }
            $city = City::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return $this->Response(true, 'City created successfully', $city, 201);
    }catch(Exception $e){
        return $this->Response(false, 'City not created', $e->getMessage(), 500);
    }
    }

    public function update(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'required|exists:cities,id',
            'name' => 'string|max:255',
            'status' => 'boolean',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole('Super Admin')){
                return $this->NotAllowed();
            }
            $city = City::findOrFail($request->id);
            $city->update($request->only(['name', 'status']));
            return $this->Response(true, 'City updated successfully', $city, 200);
    }catch(Exception $e){
    return $this->Response(false, 'City not updated', $e->getMessage(), 500);
}
}

public function delete(Request $request){
    $this->ValidateRequest($request, [
        'id' => 'required|exists:cities,id',
    ]);
    try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        if(!$user->hasRole('Super Admin')){
            return $this->NotAllowed();
        }
        $city = City::findOrFail($request->id);
        $city->delete();
        return $this->Response(true, 'City deleted successfully', null, 200);
    }catch(Exception $e){
        return $this->Response(false, 'City not deleted', $e->getMessage(), 500);
    }
}

public function list(Request $request){
    try{
        $limit = (int)$request->input('limit', 10);
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $cities = City::paginate($limit);
        $data = $this->paginateData($cities, $cities->items());
        return $this->Response(true, 'Cities list', $data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Cities not found', $e->getMessage(), 500);
    }
}
public function city_filter(Request $request){
    $this->ValidateRequest($request, [
        'status' => 'required|boolean',
    ]);
    try{
        $user = auth('api')->user();
        if(!$user){
            return $this->unauthorized();
        }
        $limit = (int)$request->input('limit', 10);
        if(!$user->hasRole(['Super Admin' , 'Admin'])){
            return $this->NotAllowed();
        }
        $cities = City::where('status', $request->status)->paginate($limit);
        $data = $this->paginateData($cities, $cities->items());
        return $this->Response(true, 'Cities list', $data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Cities not found', $e->getMessage(), 500);
    }
}
}