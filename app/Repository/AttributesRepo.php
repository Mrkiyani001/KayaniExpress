<?php 
namespace App\Repository;

use App\Models\ProductAttribute;
use App\Models\AttributeValue;
use Exception;

class AttributesRepo{
    public function create_attribute($data){
            $attribute = ProductAttribute::create([
                'name' => $data['name'],
            ]);
            return $attribute;
    }
    public function get_attributes($data){
            if(isset($data['id'])){
                $attributes = ProductAttribute::where('id', $data['id'])->with('values')->firstOrFail();
            }else{
                $attributes = ProductAttribute::with('values')->get();
            }
            return $attributes;
    }
    public function update_attribute($data){
            $attribute = ProductAttribute::findOrFail($data['id']);
            $attribute->update([
                'name' => $data['name'] ?? $attribute->name,
            ]);
            return $attribute;
    }
    public function delete_attribute($data){
            $attribute = ProductAttribute::findOrFail($data['id']);
            $attribute->values()->delete();
            $attribute->delete();
            return $attribute;
    }
    public function create_attribute_value($data){
            $attributeValue = AttributeValue::create([
                'attribute_id' => $data['attribute_id'],
                'value' => $data['value'],
            ]);
            return $attributeValue;
    }
    public function update_attribute_value($data){
            $attributeValue = AttributeValue::findOrFail($data['id']);
            $attributeValue->update([
                'value' => $data['value'] ?? $attributeValue->value,
            ]);
            return $attributeValue;
    }
    public function delete_attribute_value($data){
            $attributeValue = AttributeValue::findOrFail($data['id']);
            $attributeValue->delete();
            return $attributeValue;
    }
    public function get_attribute_values($data){
            if(isset($data['id'])){
                $attributeValues = AttributeValue::where('id', $data['id'])->with('attribute')->firstOrFail();
            }else{
                $attributeValues = AttributeValue::with('attribute')->get();
            }
            return $attributeValues;
    }
}