<?php 
namespace App\Services;

use App\Repository\AttributesRepo;
use Exception;

class AttributesService{
    private $AttributesRepo;
    public function __construct(AttributesRepo $AttributesRepo){
        $this->AttributesRepo = $AttributesRepo;
    }
    public function create_attribute($data){
        try{
            $attribute = $this->AttributesRepo->create_attribute($data);
            return $attribute;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_attributes($data){
        try{
            $attributes = $this->AttributesRepo->get_attributes($data);
            return $attributes;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function update_attribute($data){
        try{
            $attribute = $this->AttributesRepo->update_attribute($data);
            return $attribute;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_attribute($data){
        try{
            $attribute = $this->AttributesRepo->delete_attribute($data);
            return $attribute;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function create_attribute_value($data){
        try{
            $attributeValue = $this->AttributesRepo->create_attribute_value($data);
            return $attributeValue;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function update_attribute_value($data){
        try{
            $attributeValue = $this->AttributesRepo->update_attribute_value($data);
            return $attributeValue;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function delete_attribute_value($data){
        try{
            $attributeValue = $this->AttributesRepo->delete_attribute_value($data);
            return $attributeValue;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function get_attribute_values($data){
        try{
            $attributeValues = $this->AttributesRepo->get_attribute_values($data);
            return $attributeValues;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}