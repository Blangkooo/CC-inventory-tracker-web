<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'pin' => ['required', 'string', 'min:4', 'max:8'],
            'branch_id' => ['required', 'exists:branches,id'],
            'role' => ['sometimes', 'in:staff,manager'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'A 4-8 digit PIN is required for manager login.',
            'branch_id.required' => 'Please assign this manager to a branch.',
            'branch_id.exists' => 'The selected branch does not exist.',
        ];
    }
}
