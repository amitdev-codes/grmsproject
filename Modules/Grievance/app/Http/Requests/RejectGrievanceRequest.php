<?php

namespace Modules\Grievance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Director') ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:1000']];
    }
}
