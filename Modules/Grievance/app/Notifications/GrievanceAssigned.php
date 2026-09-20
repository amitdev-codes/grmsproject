<?php

namespace Modules\Grievance\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Grievance\Models\Grievance;

class GrievanceAssigned extends Notification
{
    public function __construct(public Grievance $grievance) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Investigation assigned',
            'message' => "Grievance {$this->grievance->reference_no} has been assigned to you for investigation.",
            'grievance_id' => $this->grievance->id,
            'reference_no' => $this->grievance->reference_no,
            'status' => $this->grievance->status,
            'reason' => $this->grievance->statusHistories()->latest()->value('reason'),
            'action_url' => route('grievances.edit', $this->grievance),
        ];
    }
}
