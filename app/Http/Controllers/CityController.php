<?php

namespace App\Http\Controllers;

use App\Http\Requests\City\CreateRequest;
use App\Http\Requests\City\DeleteRequest;
use App\Http\Requests\City\FilterRequest;
use App\Http\Requests\City\UpdateRequest;
use App\Models\City;
use App\Services\CityService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CityController extends BaseController
{
    private $cityService;
    public function __construct(CityService $cityService){
        $this->cityService = $cityService;
    }
    public function create(CreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $city = $this->cityService->createcity($data);
            DB::commit();
            return $this->Response(true, 'City created successfully', $city, 201);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'City not created', $e->getMessage(), 500);
    }
    }

    public function update(UpdateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $city = $this->cityService->updatecity($data);
            DB::commit();
            return $this->Response(true, 'City updated successfully', $city, 200);
    }catch(Exception $e){
        DB::rollBack();
    return $this->Response(false, 'City not updated', $e->getMessage(), 500);
}
}

public function delete(DeleteRequest $request){
    $data = $request->validated();
    try{
        DB::beginTransaction();
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        $this->authorize('checkrole', Role::class);
        $city = $this->cityService->deletecity($data);
        DB::commit();
        return $this->Response(true, 'City deleted successfully', null, 200);
    }catch(Exception $e){
        DB::rollBack();
        return $this->Response(false, 'City not deleted', $e->getMessage(), 500);
    }
}

public function list(Request $request){
    try{
        $limit = (int)$request->input('limit', 10);
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        $cities = $this->cityService->getcitylist($limit);
        $data = $this->paginateData($cities, $cities->items());
        return $this->Response(true, 'Cities list', $data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Cities not found', $e->getMessage(), 500);
    }
}
public function city_filter(FilterRequest $request){
    $data = $request->validated();
    try{
        $limit = (int)$request->input('limit', 10);
        $user = Auth::user();
        if(!$user){
            return $this->unauthorized();
        }
        $this->authorize('checkrole', Role::class);
        $cities = $this->cityService->city_filter($data , $limit);
        $data = $this->paginateData($cities, $cities->items());
        return $this->Response(true, 'Cities list', $data, 200);
    }catch(Exception $e){
        return $this->Response(false, 'Cities not found', $e->getMessage(), 500);
    }
}
}