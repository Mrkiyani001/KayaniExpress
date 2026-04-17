<?php 

namespace App\Http\Requests\ReviewRequests;

use Illuminate\Foundation\Http\FormRequest;

class GetReviewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
        ];
    }
}
