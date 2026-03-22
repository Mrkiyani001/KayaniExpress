<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'shop_id' => 'required|exists:shops,id',
            'shop_name' => 'string|max:255',
            'description' => 'string',
            'phone' => 'string|max:255',
            'city_id' => 'exists:cities,id',
            'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'banner' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ];
    }
}
