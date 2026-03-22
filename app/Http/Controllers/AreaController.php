<?php

namespace App\Http\Controllers;

use App\Http\Requests\AreaRequests\AreaFilterRequest;
use App\Http\Requests\AreaRequests\City_listRequest;
use App\Http\Requests\AreaRequests\CreateRequest;
use App\Http\Requests\AreaRequests\DeleteRequest;
use App\Http\Requests\AreaRequests\UpdateRequest;
use App\Models\Area;
use Exception;
use Illuminate\Http\Request;

class AreaController extends BaseController
{
    public function create(CreateRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::create([
                'name' => $data['name'],
                'city_id' => $data['city_id'],
                'delivery_charge' => $data['delivery_charge'],
                'status' => $data['status'],
            ]);
            return $this->Response(true, 'Area created successfully', $area, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area not created', $e->getMessage(), 500);
        }
    }
    public function update(UpdateRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::findOrFail($data['id']);
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
    public function delete(DeleteRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin','Admin'])){
                return $this->NotAllowed();
            }
            $area = Area::findOrFail($data['id']);
            $area->delete();
            return $this->Response(true, 'Area deleted successfully', null, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area not deleted', $e->getMessage(), 500);
        }
    }
    public function city_wise_list(City_listRequest $request){
        $data = $request->validated();
        try{
            $limit = (int) $request->input('limit', 10);
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $area = Area::with('city')
            ->where('city_id', $data['city_id'])
            ->where('status', 1)
            ->paginate($limit);
            $data = $this->PaginateData($area, $area->items());
            return $this->Response(true, 'Area list', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area list not found', $e->getMessage(), 500);
        }
    }
    public function area_filter(AreaFilterRequest $request){
        $data = $request->validated();
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
            ->where('city_id', $data['city_id'])
            ->where('status', $data['status'])
            ->paginate($limit);
            $data = $this->PaginateData($area, $area->items());
            return $this->Response(true, 'Area list', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area list not found', $e->getMessage(), 500);
        }
    }
}
