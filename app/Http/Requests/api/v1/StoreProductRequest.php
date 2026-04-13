<?php

namespace App\Http\Requests\api\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255|unique:products,product_name',
            'product_description' => 'required|string',
            'product_price' => 'required|numeric|min:0',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'product_brand' => 'required|string|max:255',
            'product_status' => 'nullable|boolean',
            'popular' => 'nullable|boolean',
            'category_id' => 'required|exists:categories,category_id',
        ];
    }
}
