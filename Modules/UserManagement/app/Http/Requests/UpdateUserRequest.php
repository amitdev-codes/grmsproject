<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Swap for a policy/permission check, e.g.:
        // return $this->user()->can('users.update', $this->route('user'));
        return true;
    }

    public function rules(): array
    {
        // The route param is named {user} on the resource route, bound to App\Models\User.
        $userId = $this->route('user')?->id;

        return [
            'name' => 'required|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => 'nullable|string|max:30',
            'status' => 'required|boolean',
            'bio' => 'nullable|string|max:2000',
            'district_id' => 'nullable|integer|exists:districts,id',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'role' => 'required|string|exists:roles,name',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }
}
