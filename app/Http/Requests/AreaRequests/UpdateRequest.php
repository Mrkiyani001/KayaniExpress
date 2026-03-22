<?php

namespace App\Http\Requests\AreaRequests;

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
            'id' => 'required|exists:areas,id',
            'name' => 'string|max:255',
            'city_id' => 'exists:cities,id',
            'delivery_charge' => 'numeric',
            'status' => 'boolean',
        ];
    }
}
