<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Grievance\Models\Grievance;

class BulkDeleteGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $ids = $this->input('ids');

        if (! $user?->can('grievances.delete') || ! is_array($ids) || $ids === []) {
            return false;
        }

        if ($user->hasAnyRole(['Admin', 'Super Admin'])) {
            return true;
        }

        if (! collect($ids)->every(
            fn (mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
        )) {
            return false;
        }

        return Grievance::query()
            ->whereIn('id', $ids)
            ->where('registered_by', $user->id)
            ->count() === count(array_unique($ids));
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:grievances,id'],
        ];
    }
}
