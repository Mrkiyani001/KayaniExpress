<?php 

namespace App\Http\Requests\ReviewRequests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:reviews,id',
        ];
    }
}