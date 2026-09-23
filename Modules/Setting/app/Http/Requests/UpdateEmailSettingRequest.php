<?php

namespace Modules\Setting\Http\Requests;

class UpdateEmailSettingRequest extends StoreEmailSettingRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'password' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
