<?php
namespace App\Http\Requests\AuthRequests;


use Illuminate\Foundation\Http\FormRequest;
class CheckAvailableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'nullable|required_without:phone|email',
            'phone' => 'nullable|required_without:email|numeric',
        ];
    }
}