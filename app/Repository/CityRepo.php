<?php 
namespace App\Repository;

use App\Models\City;
use Exception;

class CityRepo{
    public function createcity($data){
        
            $city = City::where('name', $data['name'])->firstOrFail();
            $city = City::create([
                'name' => $data['name'],
                'status' => $data['status'],
            ]);
            if($city){
                return $city;
            }
            throw new Exception('City not created');
        
    }
    public function updatecity($data){
            $city = City::where('id', $data['id'])->firstOrFail();
            $city->update($data);
            if($city){
                return $city;
            }
            throw new Exception('City not updated');
        
    }
    public function deletecity($data){
        
            $city = City::findOrFail($data['id']);
            $city->delete();
            if($city){
                return $city;
            }
            throw new Exception('City not deleted');   
    }
    public function getcitylist($limit){
        $city = City::where('status', 1)->paginate($limit);
        return $city;
    }
    public function city_filter($data , $limit){
        $city = City::where('status', $data['status'])->paginate($limit);
        return $city;
    }
}
