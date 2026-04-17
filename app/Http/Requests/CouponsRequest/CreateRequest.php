<?php

namespace App\Http\Requests\CouponsRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'nullable|string|max:255|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric',
            'min_order' => 'nullable|numeric',
            'max_discount' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'shop_id' => 'nullable|exists:shops,id',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ];
    }
}