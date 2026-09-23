<?php

namespace Modules\Grievance\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Grievance\Services\GrievanceCommunicationService;

class SendGrievanceCommunicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Mailtrap's testing SMTP endpoint rate-limits bursts. Retrying quickly
     * prevents a second status notification from being permanently lost.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 60, 180];
    }

    public function __construct(protected int $communicationId) {}

    public function handle(GrievanceCommunicationService $service): void
    {
        $service->deliver($this->communicationId);
    }
}
