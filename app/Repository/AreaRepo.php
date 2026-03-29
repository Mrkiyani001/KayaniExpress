<?php 
namespace App\Repository;

use App\Models\Area;
use Exception;

class AreaRepo{
    public function createArea($data){
        $area = Area::create([
                'name' => $data['name'],
                'city_id' => $data['city_id'],
                'delivery_charge' => $data['delivery_charge'],
                'status' => $data['status'],
            ]);
    if($area) {
        return $area;
    }
    throw new Exception('Area not created');
    }
    public function updateArea($data){
        $area = Area::where('id', $data['id'])->firstOrFail();
        $area->update($data);
        if($area) {
            return $area;
        }
        throw new Exception('Area not updated');
    }
    public function deleteArea($data){
        $area = Area::where('id', $data['id'])->firstOrFail();
        $area->delete();
        if($area) {
            return $area;
        }
        throw new Exception('Area not deleted');
    }
    public function city_wise_list($data, $limit){
        $area = Area::with('city')
        ->where('city_id', $data['city_id'])
        ->where('status', 1)
        ->paginate($limit);
        return $area;
    }
    public function area_filter($data, $limit){
        $area = Area::with('city')
        ->where('city_id', $data['city_id'])
        ->where('status', $data['status'])
        ->paginate($limit);
        return $area;
    }
}
