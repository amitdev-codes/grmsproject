<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Modules\Grievance\Interface\GrievanceRepositoryInterface;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Notifications\GrievanceAllocated;
use Modules\Grievance\Notifications\GrievanceReAllocationRequested;
use Illuminate\Support\Facades\DB;

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
        $this->recordAssignment($grievance, 'allocated', $actor->id, toDivisionId: $divisionId);

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
        $this->recordAssignment($grievance, 'allocated', $actor->id, toDivisionId: $grievance->division_id, toSectionId: $sectionId);

        Notification::send(
            User::role('section_manager')->where('section_id', $sectionId)->get(),
            new GrievanceAllocated($grievance)
        );

        return $grievance;
    }

    public function rejectAllocation(Grievance $grievance, User $actor, string $reason): Grievance
    {
        $from = $grievance->status;
        $grievance = $this->grievances->update($grievance, [
            'division_id' => null,
            'section_id' => null,
            'assigned_officer_id' => null,
            'status' => 'reallocation_required',
        ]);

        $this->grievances->recordStatus($grievance, $from, 'reallocation_required', $actor->id, 'section_manager', $reason);
        $this->recordAssignment($grievance, 'rejected', $actor->id, reason: $reason);

        Notification::send(
            User::role('responsible_manager')->get(),
            new GrievanceReallocationRequested($grievance)
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
        $this->recordAssignment($grievance, 'assigned', $actor->id, toDivisionId: $grievance->division_id, toSectionId: $grievance->section_id, toOfficerId: $officerId);

        $officer = User::find($officerId);
        $officer?->notify(new GrievanceAssigned($grievance));

        return $grievance;
    }

    public function reject(Grievance $grievance, User $actor, string $reason): Grievance
    {
        $from = $grievance->status;
        $grievance = $this->grievances->update($grievance, ['status' => 'rejected', 'closed_by' => $actor->id, 'closed_reason' => $reason, 'closed_at' => now()]);
        $this->grievances->recordStatus($grievance, $from, 'rejected', $actor->id, 'responsible_manager', $reason);
        $this->queueCitizenCommunication($grievance, 'status_update', "Grievance {$grievance->reference_no} cannot proceed. Reason: {$reason}");

        return $grievance;
    }

    public function close(Grievance $grievance, User $actor, string $reason): Grievance
    {
        $from = $grievance->status;
        $grievance = $this->grievances->update($grievance, ['status' => 'closed', 'closed_by' => $actor->id, 'closed_reason' => $reason, 'closed_at' => now()]);
        $this->grievances->recordStatus($grievance, $from, 'closed', $actor->id, 'responsible_manager', $reason);
        $this->queueCitizenCommunication($grievance, 'status_update', "Grievance {$grievance->reference_no} has been closed. Reason: {$reason}");

        return $grievance;
    }

    private function recordAssignment(Grievance $grievance, string $action, int $actorId, ?int $toDivisionId = null, ?int $toSectionId = null, ?int $toOfficerId = null, ?string $reason = null): void
    {
        DB::table('grievance_assignments')->insert([
            'grievance_id' => $grievance->id, 'action' => $action, 'to_division_id' => $toDivisionId,
            'to_section_id' => $toSectionId, 'to_officer_id' => $toOfficerId, 'acted_by' => $actorId,
            'reason' => $reason, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function queueCitizenCommunication(Grievance $grievance, string $messageType, string $body): void
    {
        if ($grievance->is_anonymous || (! $grievance->complainant_phone && ! $grievance->complainant_email)) {
            return;
        }

        DB::table('grievance_communications')->insert([
            'grievance_id' => $grievance->id, 'message_type' => $messageType,
            'channel' => $grievance->complainant_phone ? 'sms' : 'email',
            'recipient' => $grievance->complainant_phone ?: $grievance->complainant_email,
            'body' => $body, 'delivery_status' => 'queued',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
