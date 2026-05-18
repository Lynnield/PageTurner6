<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'format' => ['required', 'in:csv,xlsx,pdf'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string'],
            'category' => ['nullable', 'exists:categories,name'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'gt:price_min'],
            'stock_status' => ['nullable', 'in:in_stock,out_of_stock'],
        ];
    }

    public function messages(): array
    {
        return [
            'format.required' => 'Please select an export format.',
            'price_max.gt' => 'Maximum price must be greater than minimum price.',
        ];
    }
}
