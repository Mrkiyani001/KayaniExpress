<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributesController extends BaseController
{
    public function create_attribute(Request $request){
        $this->ValidateRequest($request, [
            'name' => 'required|string|max:255',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $attribute = ProductAttribute::create([
                'name' => $request->name,
            ]);
            DB::commit();
            return $this->Response(true, 'Attribute created successfully', $attribute, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute created failed', $e->getMessage(), 500);
        }
    }

    public function get_attributes(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'nullable|exists:product_attributes,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if($request->id){
                $attributes = ProductAttribute::where('id', $request->id)->with('values')->first();
            }else{
                $attributes = ProductAttribute::with('values')->get();
            }
            if($attributes){
                return $this->Response(true, 'Attributes fetched successfully', $attributes, 200);
            }else{
                return $this->Response(false, 'Attributes fetched failed', 'Attribute not found', 404);
            }
        }catch(Exception $e){
            return $this->Response(false, 'Attributes fetched failed', $e->getMessage(), 500);
        }
    }

    public function update_attribute(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'required|exists:product_attributes,id',
            'name' => 'nullable|string|max:255',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $attribute = ProductAttribute::find($request->id);
            $attribute->update([
                'name' => $request->name ?? $attribute->name,
            ]);
            DB::commit();
            return $this->Response(true, 'Attribute updated successfully', $attribute, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute updated failed', $e->getMessage(), 500);
        }
    }

    public function delete_attribute(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'required|exists:product_attributes,id',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $attribute = ProductAttribute::findOrFail($request->id);
            $attribute->values()->delete();
            $attribute->delete();
            DB::commit();
            return $this->Response(true, 'Attribute deleted successfully', $attribute, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute deleted failed', $e->getMessage(), 500);
        }
    }
    public function create_attribute_value(Request $request){
        $this->ValidateRequest($request, [
            'attribute_id' => 'required|exists:product_attributes,id',
            'value' => 'required|string|max:255',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $attributeValue = AttributeValue::create([
                'attribute_id' => $request->attribute_id,
                'value' => $request->value,
            ]);
            DB::commit();
            return $this->Response(true, 'Attribute value created successfully', $attributeValue, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute value created failed', $e->getMessage(), 500);
        }
    }
    public function update_attribute_value(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'required|exists:attribute_values,id',
            'value' => 'nullable|string|max:255',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $attributeValue = AttributeValue::findOrFail($request->id);
            $attributeValue->update([
                'value' => $request->value ?? $attributeValue->value,
            ]);
            DB::commit();
            return $this->Response(true, 'Attribute value updated successfully', $attributeValue, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute value updated failed', $e->getMessage(), 500);
        }
    }
    public function delete_attribute_value(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'required|exists:attribute_values,id',
        ]);
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if(!$user->hasRole(['Super Admin', 'Admin'])){
                return $this->NotAllowed();
            }
            $attributeValue = AttributeValue::findOrFail($request->id);
            $attributeValue->delete();
            DB::commit();
            return $this->Response(true, 'Attribute value deleted successfully', $attributeValue, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute value deleted failed', $e->getMessage(), 500);
        }
    }
    public function get_attribute_values(Request $request){
        $this->ValidateRequest($request, [
            'id' => 'nullable|exists:attribute_values,id',
        ]);
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            if($request->id){
                $attributeValues = AttributeValue::where('id', $request->id)->with('attribute')->first();
            }else{
                $attributeValues = AttributeValue::with('attribute')->get();
            }
            return $this->Response(true, 'Attribute values fetched successfully', $attributeValues, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Attribute values fetched failed', $e->getMessage(), 500);
        }
    }
}
