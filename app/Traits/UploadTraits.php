<?php 
namespace App\Traits;

trait UploadTraits{
    public function upload($file , $path){
        $file_name = time() . '.' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file_path = public_path('uploads/' . $path);
        $file->move($file_path, $file_name);
        return $file_name;
    }
}