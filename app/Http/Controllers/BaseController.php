<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    public function ValidateRequest(Request $request, array $rules)
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            abort(response()->json([       // we use abort for 422 error and return response()->json.
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422));
        }
    }
    public function unauthorized()
    {
        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }
    public function Response($status, $message, $data = null, $code)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    public function ResponseWithToken($token, $user = null)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTtl() * 36000,
            'user' => $user,
        ]);
    }
    public function NotAllowed()
    {
        return response()->json([
            'message' => 'You are not allowed to Perform these Activities',
        ], 403);
    }

    public function paginateData($paginate , $data){
        return [
            'item'=> $data,
            'pagination'=>[
                'total'=> $paginate->total(),
                'per_page'=> $paginate->perPage(),
                'current_page'=> $paginate->currentPage(),
                'last_page'=> $paginate->lastPage(),
                'from'=> $paginate->firstItem(),
                'to'=> $paginate->lastItem(),
                'has_more'=> $paginate->hasMorePages(),
            ]
        ];
    }
}
