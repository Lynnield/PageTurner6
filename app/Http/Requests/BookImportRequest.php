<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookImportRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:102400'],
            'mode' => ['required', 'in:create,update,upsert'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'File must be CSV or XLSX format.',
            'file.max' => 'File must not exceed 100MB.',
            'mode.required' => 'Please select an import mode.',
            'mode.in' => 'Invalid import mode selected.',
        ];
    }
}
