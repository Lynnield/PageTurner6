<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'format' => ['required', Rule::in(['csv', 'xlsx', 'pdf'])],
            'category' => ['nullable', 'string', 'max:255'],
            'price_min' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'stock_status' => ['nullable', Rule::in(['in_stock', 'out_of_stock'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'columns' => ['nullable', 'string'], // comma-separated
        ];
    }
}

