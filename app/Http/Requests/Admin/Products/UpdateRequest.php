<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'thumbnail_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'demo_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'source_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'description' => ['required', 'string'],
        ];
    }
}
