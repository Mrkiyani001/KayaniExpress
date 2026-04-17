<?php 
namespace App\Http\Requests\ReviewRequests;

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
            'product_id' => 'required|exists:products,id',
            'order_item_id' => 'required|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:20480',
            'is_approved' => 'boolean|default:false',
        ];
    }
    public function messages()
    {
        return [
            'product_id.required' => 'Product ID is required',
            'product_id.exists' => 'Product ID does not exist',
            'order_item_id.required' => 'Order Item ID is required',
            'order_item_id.exists' => 'Order Item ID does not exist',
            'rating.required' => 'Rating is required',
            'rating.integer' => 'Rating must be an integer',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating must be at most 5',
            'comment.string' => 'Comment must be a string',
            'comment.max' => 'Comment must be at most 255 characters',
            'images.array' => 'Images must be an array',
            'images.*.image' => 'Images must be an image',
            'images.*.mimes' => 'Images must be a valid image format',
            'images.*.max' => 'Images must be at most 2MB',
            'is_approved.boolean' => 'Is Approved must be a boolean',
        ];
    }
}