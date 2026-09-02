<?php

namespace Modules\Grievance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrievanceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name_en,
            'name_st' => $this->name_st,
            'slug' => $this->code,
            'icon' => $this->icon ?? null, // add `icon` column to grievance_categories if not present
            'is_sensitive' => (bool) ($this->is_sensitive ?? false), // add column if you want this flagged in the UI
            'division_id' => $this->division_id,
            'form_fields' => $this->form_fields ?? null, // add nullable json column if you want per-category extra fields
        ];
    }
}
