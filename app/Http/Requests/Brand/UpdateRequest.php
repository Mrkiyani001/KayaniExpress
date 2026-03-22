<?php

namespace App\Http\Requests\Brand;

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
            'brand_id'=>'required|exists:brands,id',
            'name'=>'nullable|string|max:255',
            'logo'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'=>'nullable|in:active,inactive',
        ];
    }
}
