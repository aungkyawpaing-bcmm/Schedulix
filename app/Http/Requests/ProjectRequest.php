<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->route('project') ? 'update' : 'create', $this->route('project') ?? \App\Models\Project::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('projects', 'code')->ignore($this->route('project'))],
            'project_manager_id' => ['required', 'exists:users,id'],
            'expected_start_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date', 'after_or_equal:expected_start_date'],
            'overview' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'team_size' => ['nullable', 'integer', 'min:1'],
            'timezone' => ['required', 'timezone'],
            'status' => ['required', Rule::in(config('wbs.project_statuses'))],
            'locale_default' => ['required', Rule::in(array_keys(config('wbs.supported_locales')))],
        ];
    }
}
