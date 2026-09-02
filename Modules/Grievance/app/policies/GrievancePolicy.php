<?php

namespace Modules\Grievance\policies;

use Modules\Grievance\Models\Grievance;
use App\Models\User;

class GrievancePolicy
{
    // Responsible Manager: any grievance still at 'submitted' with no division yet
    public function allocateDivision(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('responsible_manager')
            && $grievance->status === 'submitted'
            && is_null($grievance->division_id);
    }

    // Division Director: only grievances already in THEIR division
    public function allocateSection(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('division_director')
            && $grievance->status === 'allocated_division'
            && $grievance->division_id === $user->division_id;
    }

    // Section Manager: reject an allocation into THEIR section, sends back up
    public function rejectAllocation(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('section_manager')
            && $grievance->status === 'allocated_section'
            && $grievance->section_id === $user->section_id;
    }

    // Section Manager: assign an officer within THEIR section
    public function assignOfficer(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('section_manager')
            && $grievance->status === 'allocated_section'
            && $grievance->section_id === $user->section_id;
    }

    public function view(User $user, Grievance $grievance): bool
    {
        if ($user->hasAnyRole(['super_admin', 'grms_admin', 'supervisory_authority'])) {
            return true;
        }

        return match (true) {
            $user->hasRole('division_director') => $grievance->division_id === $user->division_id,
            $user->hasRole('section_manager') => $grievance->section_id === $user->section_id,
            $user->hasRole('grievance_officer') => $grievance->assigned_officer_id === $user->id,
            $user->hasRole('responsible_manager') => true, // sees the whole intake queue
            default => false,
        };
    }
}
