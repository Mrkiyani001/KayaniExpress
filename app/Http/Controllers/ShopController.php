<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ApplyRequest;
use App\Http\Requests\Shop\ApproveRequest;
use App\Http\Requests\Shop\RejectRequest;
use App\Http\Requests\Shop\SuspendRequest;
use App\Http\Requests\Shop\UnSuspendRequest;
use App\Http\Requests\Shop\UpdateRequest;
use App\Services\ShopService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ShopController extends BaseController
{
    public function __construct(public ShopService $shopService){
        $this->shopService = $shopService;
    }
    public function apply(ApplyRequest $request)
    {
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = $this->shopService->apply($data , $user , $request);
            DB::commit();
            return $this->Response(true, 'Shop applied successfully',$shop, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function approve(ApproveRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $shop = $this->shopService->approve($data);
            DB::commit();
            return $this->Response(true, 'Shop approved successfully',$shop, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function reject(RejectRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $shop = $this->shopService->reject($data);
            DB::commit();
            return $this->Response(true, 'Shop rejected successfully',$shop, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function suspend(SuspendRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $shop = $this->shopService->suspend($data);
            DB::commit();
            return $this->Response(true, 'Shop suspended successfully',$shop, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function unsuspend(UnSuspendRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $this->authorize('checkrole', Role::class);
            $shop = $this->shopService->unsuspend($data);
            DB::commit();
            return $this->Response(true, 'Shop unsuspended successfully',$shop, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function myshop(Request $request){
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = $this->shopService->myshop($user);
            return $this->Response(true, 'Shop fetched successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function updateShop(UpdateRequest $request){
        $data = $request->validated();
        try{
            DB::beginTransaction();
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = $this->shopService->updateShop($data , $user , $request);
            DB::commit();
            return $this->Response(true, 'Shop updated successfully',$shop, 200);
        }catch(Exception $e){
            DB::rollBack();
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function shopdetail($slug){
        try{
            $shop = $this->shopService->shopdetail($slug);
            return $this->Response(true, 'Shop fetched successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
    public function shoplist(Request $request){
        try{
            $user = Auth::user();
            if(!$user){
                return $this->unauthorized();
            }
            $shop = $this->shopService->shoplist($user);
            return $this->Response(true, 'Shop list fetched successfully',$shop, 200);
        }catch(Exception $e){
            return $this->Response(false, $e->getMessage(),[], 500);
        }
    }
}
