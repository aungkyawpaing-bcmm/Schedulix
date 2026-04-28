<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->system_role === 'owner';
    }

    public function rules(): array
    {
        $user = $this->route('pic');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'position' => ['nullable', 'string', 'max:255'],
            'system_role' => ['required', Rule::in(config('wbs.system_roles'))],
            'locale' => ['required', Rule::in(array_keys(config('wbs.supported_locales')))],
            'timezone' => ['required', 'timezone'],
            'is_active' => ['required', 'boolean'],
            'is_available' => ['required', 'boolean'],
            'available_from' => ['nullable', 'date'],
        ];
    }
}
