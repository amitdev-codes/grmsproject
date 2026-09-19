<?php

namespace Modules\Grievance\policies;

use Modules\Grievance\Models\Grievance;
use App\Models\User;

class GrievancePolicy
{
    // Director: any grievance still at 'submitted' with no division yet
    public function allocateDivision(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('Director')
            && $grievance->status === 'submitted'
            && is_null($grievance->division_id);
    }

    // Division Director: only grievances already in THEIR division
    public function allocateSection(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('Division Director')
            && $grievance->status === 'allocated_division'
            && $grievance->division_id === $user->division_id;
    }

    // Section Manager: reject an allocation into THEIR section, sends back up
    public function rejectAllocation(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('Section Manager')
            && $grievance->status === 'allocated_section'
            && $grievance->section_id === $user->section_id;
    }

    // Section Manager: assign an officer within THEIR section
    public function assignOfficer(User $user, Grievance $grievance): bool
    {
        return $user->hasRole('Section Manager')
            && $grievance->status === 'allocated_section'
            && $grievance->section_id === $user->section_id;
    }

    public function viewTriageQueue(User $user): bool
    {
        return $user->hasAnyRole(['Director', 'Super Admin']);
    }

    public function view(User $user, Grievance $grievance): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'IT Admin', 'Director'])) {
            return true;
        }

        return match (true) {
            $user->hasRole('Division Director') => $grievance->division_id === $user->division_id,
            $user->hasRole('Section Manager') => $grievance->section_id === $user->section_id,
            $user->hasRole('Helpdesk Officer') => $grievance->assigned_officer_id === $user->id,
            $user->hasRole('Director') => true, // sees the whole intake queue
            default => false,
        };
    }
}
