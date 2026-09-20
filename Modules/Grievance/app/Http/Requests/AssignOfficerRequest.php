<?php

namespace Modules\Grievance\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AssignOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->hasRole('Section Manager')
            && $user->section_id
            && User::role('Helpdesk Officer')
                ->where('section_id', $user->section_id)
                ->whereKey($this->input('officer_id'))
                ->exists();
    }

    public function rules(): array
    {
        return [
            'officer_id' => ['required', 'exists:users,id'],
        ];
    }
}
