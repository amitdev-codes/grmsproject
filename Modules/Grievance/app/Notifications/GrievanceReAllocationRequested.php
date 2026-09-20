<?php

namespace Modules\Grievance\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Grievance\Models\Grievance;

class GrievanceReAllocationRequested extends Notification
{
    public function __construct(public Grievance $grievance) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Reallocation required',
            'message' => "Grievance {$this->grievance->reference_no} was returned for reallocation.",
            'grievance_id' => $this->grievance->id,
            'reference_no' => $this->grievance->reference_no,
            'status' => $this->grievance->status,
            'reason' => $this->grievance->statusHistories()->latest()->value('reason'),
            'action_url' => route('grievances.edit', $this->grievance),
        ];
    }
}
