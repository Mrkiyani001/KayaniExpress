<?php 
namespace App\Traits;

trait UploadTraits{
    public function upload($file , $path){
        $file_name = time() . '.' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file_path = public_path('uploads/' . $path);
        $file->move($file_path, $file_name);
        return $file_name;
    }
    public function product_image($file,$folder,$model){
        $file_name = time().'.'.uniqid().'.'.$file->getClientOriginalExtension();
        $file_path = public_path('uploads/'.$folder);
        $extension = strtolower($file->getClientOriginalExtension());
        $type = $this->getFileType($extension);
        $file->move($file_path,$file_name);
        $model->attachment()->create([
            'file_name' => $file_name,
            'file_path' => $file_path,
            'file_type' => $type,
        ]);
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