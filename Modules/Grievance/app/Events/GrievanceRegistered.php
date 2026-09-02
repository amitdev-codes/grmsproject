<?php

namespace Modules\Grievance\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Grievance\Models\Grievance;

class GrievanceRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(public Grievance $grievance)
    {
    }
}
