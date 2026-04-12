<?php

namespace App\Http\Requests\api\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        // $this->route('category') now returns the Category MODEL instance
        // because of Laravel's automatic Route Model Binding.
        $category = $this->route('category');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                // Ignore the current record by its ID
                'unique:categories,name,' . $category->category_id . ',category_id'
            ],
            'description' => 'nullable|string|max:500',
        ];
    }
}
