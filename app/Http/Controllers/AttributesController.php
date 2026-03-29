<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attribute\CreateRequest;
use App\Http\Requests\Attribute\DeleteRequest;
use App\Http\Requests\Attribute\GetAttRequest;
use App\Http\Requests\Attribute\UpdateRequest;
use App\Http\Requests\AttributeValue\CreateRequest as AttributeValueCreateRequest;
use App\Http\Requests\AttributeValue\DeleteRequest as AttributeValueDeleteRequest;
use App\Http\Requests\AttributeValue\GetAttRequest as AttributeValueGetAttRequest;
use App\Http\Requests\AttributeValue\UpdateRequest as AttributeValueUpdateRequest;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use App\Services\AttributesService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AttributesController extends BaseController
{
    private $AttributesService;
    public function __construct(AttributesService $AttributesService){
        $this->AttributesService = $AttributesService;
    }
    public function create_attribute(CreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $attribute = $this->AttributesService->create_attribute($data);
            DB::commit();
            return $this->Response(true, 'Attribute created successfully', $attribute, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute created failed', $e->getMessage(), 500);
        }
    }

    public function get_attributes(GetAttRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $attributes = $this->AttributesService->get_attributes($data);
            return $this->Response(true, 'Attributes fetched successfully', $attributes, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Attributes fetched failed', $e->getMessage(), 500);
        }
    }

    public function update_attribute(UpdateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $attribute = $this->AttributesService->update_attribute($data);
            DB::commit();
            return $this->Response(true, 'Attribute updated successfully', $attribute, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute updated failed', $e->getMessage(), 500);
        }
    }

    public function delete_attribute(DeleteRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $attribute = $this->AttributesService->delete_attribute($data);
            DB::commit();
            return $this->Response(true, 'Attribute deleted successfully', $attribute, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute deleted failed', $e->getMessage(), 500);
        }
    }
    public function create_attribute_value(AttributeValueCreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $attributeValue = $this->AttributesService->create_attribute_value($data);
            DB::commit();
            return $this->Response(true, 'Attribute value created successfully', $attributeValue, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute value created failed', $e->getMessage(), 500);
        }
    }
    public function update_attribute_value(AttributeValueUpdateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $attributeValue = $this->AttributesService->update_attribute_value($data);
            DB::commit();
            return $this->Response(true, 'Attribute value updated successfully', $attributeValue, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute value updated failed', $e->getMessage(), 500);
        }
    }
    public function delete_attribute_value(AttributeValueDeleteRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $attributeValue = $this->AttributesService->delete_attribute_value($data);
            DB::commit();
            return $this->Response(true, 'Attribute value deleted successfully', $attributeValue, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Attribute value deleted failed', $e->getMessage(), 500);
        }
    }
    public function get_attribute_values(AttributeValueGetAttRequest $request){
        $data = $request->validated();
        try{
            $user = auth('api')->user();
            if(!$user){
                return $this->unauthorized();
            }
            $attributeValues = $this->AttributesService->get_attribute_values($data);
            return $this->Response(true, 'Attribute values fetched successfully', $attributeValues, 200);
        }catch(Exception $e){
            return $this->Response(false, 'Attribute values fetched failed', $e->getMessage(), 500);
        }
    }
}
