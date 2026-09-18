<?php

namespace Modules\Grievance\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Grievance\Services\GrievanceRegistrationService;

class SubmitGrievanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        protected int $grievanceId,
    ) {}

    public function handle(GrievanceRegistrationService $service): void
    {
        $service->processAsync($this->grievanceId);
    }
}