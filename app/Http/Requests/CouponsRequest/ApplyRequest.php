<?php

namespace App\Http\Requests\CouponsRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:coupons,code',
            'order_amount' => 'required|numeric|min:0',
        ];
    }
}
