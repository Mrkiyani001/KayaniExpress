<?php 
namespace App\Services;

use App\Repository\CityRepo;
use Exception;

class CityService{
    private $cityRepo;
    public function __construct(CityRepo $cityRepo){
        $this->cityRepo = $cityRepo;
    }
    public function createcity($data){
        try{
            $city = $this->cityRepo->createcity($data);
            return $city;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function updatecity($data){
        try{
            $city = $this->cityRepo->updatecity($data);
            return $city;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function deletecity($data){
        try{
            $city = $this->cityRepo->deletecity($data);
            return $city;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function getcitylist($limit){
        try{
            $city = $this->cityRepo->getcitylist($limit);
            return $city;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function city_filter($data , $limit){
        try{
            $city = $this->cityRepo->city_filter($data , $limit);
            return $city;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}