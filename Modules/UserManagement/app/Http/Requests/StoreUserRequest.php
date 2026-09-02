<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Swap for a policy/permission check, e.g.:
        // return $this->user()->can('users.create');
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'status' => 'required|boolean',
            'bio' => 'nullable|string|max:2000',
            'district_id' => 'nullable|integer|exists:districts,id',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'role' => 'required|string|exists:roles,name',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
