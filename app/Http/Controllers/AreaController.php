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
use Spatie\Permission\Models\Role;
use App\Services\AreaService;
use Illuminate\Support\Facades\DB;

class AreaController extends BaseController
{
    private $areaService;
    public function __construct(AreaService $areaService){
        $this->areaService = $areaService;
    }
    public function create(CreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $area = $this->areaService->createArea($data);
            DB::commit();
            return $this->Response(true, 'Area created successfully', $area, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Area not created', $e->getMessage(), 500);
        }
    }
    public function update(UpdateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
           $area = $this->areaService->updateArea($data);
           DB::commit();
            return $this->Response(true, 'Area updated successfully', $area, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Area not updated', $e->getMessage(), 500);
        }
    }
    public function delete(DeleteRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $area = $this->areaService->deleteArea($data);
            DB::commit();
            return $this->Response(true, 'Area deleted successfully', $area, 200);
        }catch(Exception $e){
            DB::rollBack();
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
            $area = $this->areaService->city_wise_list($data, $limit);
            $data = $this->PaginateData($area, $area->items());
            return $this->Response(true, 'Area list', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area list not found', $e->getMessage(), 500);
        }
    }
    public function area_filter(AreaFilterRequest $request){
        $data = $request->validated();
        try{
            $limit = (int) $request->input('limit', 10);
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $area = $this->areaService->area_filter($data, $limit);
            $data = $this->PaginateData($area, $area->items());
            return $this->Response(true, 'Area list', $data, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Area list not found', $e->getMessage(), 500);
        }
    }
}
