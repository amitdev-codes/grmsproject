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
            'grievance_id' => $this->grievance->id,
            'reference_no' => $this->grievance->reference_no,
            'reason'=> $this->grievance->statusHistories()->latest()->value('reason')
            ];
    }
}
