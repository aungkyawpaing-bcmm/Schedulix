<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'export_type' => ['required', Rule::in(['xlsx'])],
            'include_formula' => ['required', 'boolean'],
            'include_critical_path' => ['nullable', 'boolean'],
            'export_locale' => ['nullable', Rule::in(array_keys(config('wbs.supported_locales')))],
            'file_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
