<?php

namespace App\Http\Requests\api\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
    // $this->route('product') gets the Product object from the URL slug
    $product = $this->route('product');

    return [
        'product_name' => 'sometimes|required|string|max:255|unique:products,product_name,' . $product->product_id . ',product_id',
        'product_description' => 'sometimes|required|string',
        'product_price' => 'sometimes|required|numeric|min:0',
        'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'product_brand' => 'sometimes|required|string|max:255',
        'product_status' => 'nullable|boolean',
        'popular' => 'nullable|boolean',
        'category_id' => 'sometimes|required|exists:categories,category_id',
    ];
}
}
