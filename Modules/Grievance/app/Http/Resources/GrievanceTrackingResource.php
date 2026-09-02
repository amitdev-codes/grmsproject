<?php

namespace Modules\Grievance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Grievance\Models\Grievance;

class GrievanceTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reference_number' => $this->reference_no,
            'status' => $this->status,
            'priority' => $this->priority,
            'category_name' => $this->category?->name_en,
            'division_name' => $this->division?->name ?? null,
            'section_name' => $this->section?->name ?? null,
            'description' => $this->description,
            'location_description' => $this->location_description,
            'sla_due_at' => $this->sla_due_at?->toIso8601String(),
            'satisfaction_rating' => $this->satisfaction_rating,
            'history' => $this->statusHistories->map(fn ($h) => [
                'id' => $h->id,
                'from_status' => $h->from_status,
                'to_status' => $h->to_status,
                'note' => $h->reason,
                'changed_at' => $h->created_at->toIso8601String(),
            ]),
            'messages' => $this->messages->map(fn ($m) => [
                'id' => $m->id,
                'sender' => $m->sender,
                'body' => $m->body,
                'created_at' => $m->created_at->toIso8601String(),
            ]),
            'escalation' => null, // populated once the movement/escalation module exists
            'resolution' => null, // populated once the movement module writes a resolution record
            'attachments' => $this->getMedia(Grievance::MEDIA_COLLECTION)->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'original_filename' => $m->file_name,
            ]),
        ];
    }
}
