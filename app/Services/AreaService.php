<?php 
namespace App\Services;

use App\Repository\AreaRepo;

class AreaService{
    private $areaRepo;
    public function __construct(AreaRepo $areaRepo){
        $this->areaRepo = $areaRepo;
    }
    public function createArea($data){
        $area = $this->areaRepo->createArea($data);
        return $area;
    }
    public function updateArea($data){
        $area = $this->areaRepo->updateArea($data);
        return $area;
    }
    public function deleteArea($data){
        $area = $this->areaRepo->deleteArea($data);
        return $area;
    }
    public function city_wise_list($data, $limit){
        $area = $this->areaRepo->city_wise_list($data, $limit);
        return $area;
    }
    public function area_filter($data, $limit){
        $area = $this->areaRepo->area_filter($data, $limit);
        return $area;
    }
}