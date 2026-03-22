<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id'     => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cash_on_delivery,online',
        ];
    }
}
