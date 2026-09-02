<?php

namespace Modules\Grievance\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class BulkDeleteGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievance.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:grievances,id'],
        ];
    }
}
