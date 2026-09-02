<?php

namespace Modules\Grievance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Grievance\Models\Grievance;

class GrievanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'description' => $this->description,
            'status' => $this->status,
            'is_anonymous' => $this->is_anonymous,
            'complainant_name' => $this->complainant_name,
            'complainant_phone' => $this->complainant_phone,
            'complainant_email' => $this->complainant_email,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id, 'code' => $this->category->code, 'name_en' => $this->category->name_en,
            ]),
            'channel' => $this->whenLoaded('channel', fn () => [
                'id' => $this->channel->id, 'code' => $this->channel->code, 'name' => $this->channel->name,
            ]),
            'district' => $this->whenLoaded('district', fn () => $this->district ? [
                'id' => $this->district->id, 'name' => $this->district->name,
            ] : null),
            'attachments' => $this->getMedia(Grievance::MEDIA_COLLECTION)->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'file_name' => $m->file_name,
                'mime_type' => $m->mime_type,
                'size' => $m->size,
                'url' => $m->getUrl(),
                'thumb_url' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
