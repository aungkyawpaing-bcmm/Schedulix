<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WbsItemRequest extends FormRequest
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
            'parent_id' => ['nullable', 'exists:project_wbs_items,id'],
            'task_master_id' => ['nullable', 'exists:task_master,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'item_type' => ['required', Rule::in(config('wbs.wbs_item_types'))],
            'content_item_type' => ['nullable', Rule::in(config('wbs.content_item_types'))],
            'platform' => ['nullable', Rule::in(config('wbs.platforms'))],
            'description' => ['nullable', 'string'],
            'is_assignable' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
