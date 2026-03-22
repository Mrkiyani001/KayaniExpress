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

    public function PaginateData($paginate , $data){
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
    public function upload($file , $path){
        $file_name = time() . '.' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file_path = public_path('uploads/' . $path);
        $file->move($file_path, $file_name);
        return $file_name;
    }
    public function product_file($file, $folder, $model){
        $file_name = time() . '.' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file_path = public_path('uploads/' . $folder);
        $extension = strtolower($file->getClientOriginalExtension());
        $type = $this->getFileType($extension);
        $file->move($file_path, $file_name);
        $model->attachment()->create([
            'file_name' => $file_name,
            'file_path' => $file_path,
            'file_type' => $type,
        ]);
        return;
    }
    private function getFileType($extension)
    {
       $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'heic', 'heif'];
       $documentExtensions = [
        'pdf', 
        'doc', 'docx',       // Word
        'xls', 'xlsx', 'csv', // Excel
        'ppt', 'pptx',       // PowerPoint
        'txt', 'rtf', 'json' // Text/Data
    ];
    $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp'];
    $audioExtensions = ['mp3', 'wav', 'aac', 'ogg', 'm4a', 'wma', 'amr'];
    $archiveExtensions = ['zip', 'rar', '7z'];
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $documentExtensions)) {
            return 'document';
        } elseif (in_array($extension, $videoExtensions)) {
            return 'video';
        } elseif (in_array($extension, $audioExtensions)) {
            return 'audio';
        } elseif(in_array($extension, $archiveExtensions)){
            return 'archive';
        }else{
            return 'other';
        }
    }
}
