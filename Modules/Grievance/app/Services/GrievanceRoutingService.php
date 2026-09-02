<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Notifications\GrievanceAllocated;

class GrievanceRoutingService
{
    public function __construct(protected GrievanceRepositoryInterface $grievances)
    {
    }

    public function queueForResponsibleManager(int $perPage = 20)
    {
        return $this->grievances->queueForResponsibleManager($perPage);
    }

    public function queueForDivision(int $divisionId, int $perPage = 20)
    {
        return $this->grievances->queueForDivision($divisionId, $perPage);
    }

    public function queueForSection(int $sectionId, int $perPage = 20)
    {
        return $this->grievances->queueForSection($sectionId, $perPage);
    }

    public function allocateToDivision(Grievance $grievance, int $divisionId, User $actor): Grievance
    {
        $from = $grievance->status;

        $grievance = $this->grievances->update($grievance, [
            'division_id' => $divisionId,
            'status' => 'allocated_division',
        ]);

        $this->grievances->recordStatus($grievance, $from, 'allocated_division', $actor->id, 'responsible_manager');

        Notification::send(
            User::role('division_director')->where('division_id', $divisionId)->get(),
            new GrievanceAllocated($grievance)
        );

        return $grievance;
    }

    public function allocateToSection(Grievance $grievance, int $sectionId, User $actor): Grievance
    {
        $from = $grievance->status;

        $grievance = $this->grievances->update($grievance, [
            'section_id' => $sectionId,
            'status' => 'allocated_section',
        ]);

        $this->grievances->recordStatus($grievance, $from, 'allocated_section', $actor->id, 'division_director');

        Notification::send(
            User::role('section_manager')->where('section_id', $sectionId)->get(),
            new GrievanceAllocated($grievance)
        );

        return $grievance;
    }

    public function rejectAllocation(Grievance $grievance, User $actor, string $reason): Grievance
    {
        $from = $grievance->status;
        $divisionId = $grievance->division_id;

        $grievance = $this->grievances->update($grievance, [
            'section_id' => null,
            'status' => 'allocated_division',
        ]);

        $this->grievances->recordStatus($grievance, $from, 'allocated_division', $actor->id, 'section_manager', $reason);

        Notification::send(
            User::role('division_director')->where('division_id', $divisionId)->get(),
            new GrievanceReallocationRequested($grievance, $reason)
        );

        return $grievance;
    }

    public function assignOfficer(Grievance $grievance, int $officerId, User $actor): Grievance
    {
        $from = $grievance->status;

        $grievance = $this->grievances->update($grievance, [
            'assigned_officer_id' => $officerId,
            'status' => 'assigned_officer',
        ]);

        $this->grievances->recordStatus($grievance, $from, 'assigned_officer', $actor->id, 'section_manager');

        $officer = User::find($officerId);
        $officer?->notify(new GrievanceAssigned($grievance));

        return $grievance;
    }
}
