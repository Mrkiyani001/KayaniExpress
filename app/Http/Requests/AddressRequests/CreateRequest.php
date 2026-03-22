<?php

namespace App\Http\Requests\AddressRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name'=>'required|string|max:255',
            'type'=>'required|in:home,work,other',
            'city_id'=>'required|exists:cities,id',
            'area_id'=>'required|exists:areas,id',
            'phone'=>'required|string|max:255',
            'address_line'=>'required|string|max:255',
            'is_default'=>'required|boolean',
        ];
    }
}
