<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'scope_type' => ['required', Rule::in(['global', 'project'])],
            'project_id' => ['nullable', 'exists:projects,id'],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'lunch_start_time' => ['nullable', 'date_format:H:i'],
            'lunch_end_time' => ['nullable', 'date_format:H:i', 'after:lunch_start_time'],
            'net_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'is_working_day' => ['required', 'boolean'],
        ];
    }
}
