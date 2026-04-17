<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequests\CreateRequest;
use App\Http\Requests\AddressRequests\DeleteRequest;
use App\Http\Requests\AddressRequests\SetRequest;
use App\Http\Requests\AddressRequests\UpdateRequest;
use Exception;
use Illuminate\Http\Request;
use App\Services\AddressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends BaseController
{
    private $addressService;
    public function __construct(AddressService $addressService){
        $this->addressService = $addressService;
    }
    public function create(CreateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = $this->addressService->createaddress($data , $user);
            DB::commit();
            return $this->Response(true, 'Address created successfully', $address,200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Failed to create address', null,500);
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
            $address = $this->addressService->updateaddress($data , $user);
            DB::commit();
            return $this->Response(true, 'Address updated successfully', $address,200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Failed to update address', null,500);
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
            $address = $this->addressService->deleteaddress($data , $user);
            DB::commit();
            return $this->Response(true, 'Address deleted successfully', null,200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, 'Failed to delete address', null,500);
        }
    }

    public function list(Request $request){
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $addresses = $this->addressService->getAddresses($user);
            return $this->Response(true, 'Addresses fetched successfully', $addresses,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to fetch addresses', null,500);
        }
    }
    public function setDefault(SetRequest $request){
        $data = $request->validated();
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $address = $this->addressService->setDefault($data , $user);
            return $this->Response(true, 'Address set as default successfully', $address,200);
        }catch(Exception $e){
            return $this->Response(false, 'Failed to set address as default', null,500);
        }
    }
}
