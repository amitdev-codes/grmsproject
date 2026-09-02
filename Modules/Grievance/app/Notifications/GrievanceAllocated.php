<?php

namespace Modules\Grievance\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Grievance\Models\Grievance;

class GrievanceAllocated extends Notification
{
    public function __construct(public Grievance $grievance) {}
    public function via($notifiable) { return ['database']; }
    public function toArray($notifiable)
    {
        return ['grievance_id' => $this->grievance->id, 'reference_no' => $this->grievance->reference_no];
    }
}
