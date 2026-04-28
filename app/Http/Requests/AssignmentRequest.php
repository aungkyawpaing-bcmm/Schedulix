<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignmentRequest extends FormRequest
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
            'project_manager_id' => ['required', 'exists:users,id'],
            'project_leader_ids' => ['nullable', 'array'],
            'project_leader_ids.*' => ['exists:users,id'],
            'project_wbs_item_id' => ['required', 'exists:project_wbs_items,id'],
            'depends_on_assignment_id' => ['nullable', 'exists:assignments,id'],
            'priority' => ['required', Rule::in(config('wbs.priority_levels'))],
            'planned_hours' => ['required', 'numeric', 'gt:0'],
            'assigned_pic_id' => ['required', 'exists:users,id'],
            'leave_dates' => ['nullable', 'array'],
            'leave_dates.*' => ['date'],
            'assigned_role' => ['required', Rule::in(config('wbs.system_roles'))],
            'remark' => ['nullable', 'string'],
            'auto_create_schedule' => ['required', 'boolean'],
            'status' => ['nullable', Rule::in(['draft', 'scheduled', 'ongoing', 'completed'])],
            'is_critical' => ['nullable', 'boolean'],
        ];
    }
}
