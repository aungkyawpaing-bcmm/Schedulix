<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'task_code' => ['required', 'string', 'max:50', Rule::unique('task_master', 'task_code')->ignore($this->route('task_master'))],
            'name' => ['required', 'string', 'max:255'],
            'content_item_type' => ['required', Rule::in(config('wbs.content_item_types'))],
            'platform' => ['required', Rule::in(config('wbs.platforms'))],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
