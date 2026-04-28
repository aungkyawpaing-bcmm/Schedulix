<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
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
            'default_locale' => ['required', Rule::in(array_keys(config('wbs.supported_locales')))],
            'default_timezone' => ['required', 'timezone'],
            'date_format' => ['required', Rule::in(['Y-m-d', 'd/m/Y', 'm/d/Y'])],
            'rows_per_page' => ['required', Rule::in([10, 25, 50, 100])],
        ];
    }
}
