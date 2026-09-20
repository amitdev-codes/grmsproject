<?php

namespace Modules\Grievance\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Grievance\Models\Grievance;

class GrievanceAllocated extends Notification
{
    public function __construct(public Grievance $grievance) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $destination = $this->grievance->section?->name
            ?? $this->grievance->division?->name
            ?? 'the grievance team';

        return [
            'title' => 'New grievance allocation',
            'message' => "Grievance {$this->grievance->reference_no} has been allocated to {$destination} and is ready for action.",
            'grievance_id' => $this->grievance->id,
            'reference_no' => $this->grievance->reference_no,
            'status' => $this->grievance->status,
            'action_url' => route('grievances.edit', $this->grievance),
        ];
    }
}
