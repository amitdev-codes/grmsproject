<?php

namespace Modules\Grievance\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceWorkflowStep;

class GrievanceWorkflowActionRequired extends Notification
{
    public function __construct(
        public Grievance $grievance,
        public GrievanceWorkflowStep $step,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Grievance action required',
            'message' => "Grievance {$this->grievance->reference_no} is awaiting action at {$this->step->name}.",
            'grievance_id' => $this->grievance->id,
            'reference_no' => $this->grievance->reference_no,
            'status' => $this->grievance->status,
            'workflow_step_id' => $this->step->id,
            'action_url' => route('grievances.edit', $this->grievance),
        ];
    }
}
