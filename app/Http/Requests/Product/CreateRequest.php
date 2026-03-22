<?php

namespace App\Http\Requests\Product;

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
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file'=>'nullable|array',
            'file.*' => 'file|mimes:mp4,mov,avi,wmv,flv,mkv,webm,3gp,jpeg,png,gif,webp,bmp,svg,heic,heif|max:102400',
            'is_featured' => 'nullable|boolean',
            // SKUs validation
            'skus' => 'required|array|min:1',
            'skus.*.price' => 'required|numeric|min:0',
            'skus.*.discounted_price' => 'nullable|numeric|min:0',
            'skus.*.stock_qty' => 'required|integer|min:0',
            'skus.*.attribute_values' => 'required|array', // e.g. {"Color": "Red", "Size": "XL"}
        
        ];
    }
}
