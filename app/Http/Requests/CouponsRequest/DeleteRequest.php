<?php

namespace App\Http\Requests\CouponsRequest;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:coupons,id',
        ];
    }
}
