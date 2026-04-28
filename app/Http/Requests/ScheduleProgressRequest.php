<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleProgressRequest extends FormRequest
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
            'actual_hours' => ['nullable', 'array'],
            'actual_hours.*' => ['array'],
            'actual_hours.*.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'plan_rest_hours' => ['nullable', 'array'],
            'plan_rest_hours.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'actual_start_dates' => ['nullable', 'array'],
            'actual_start_dates.*' => ['nullable', 'date'],
            'actual_end_dates' => ['nullable', 'array'],
            'actual_end_dates.*' => ['nullable', 'date'],
        ];
    }
}
