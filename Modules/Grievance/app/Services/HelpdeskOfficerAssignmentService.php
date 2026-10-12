<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Grievance\Models\Grievance;

class HelpdeskOfficerAssignmentService
{
    public function assignFor(Grievance $grievance, ?int $approverUserId = null): ?User
    {
        $query = User::query()
            ->where('status', true)
            ->whereHas('roles', fn (Builder $roles) => $roles->where('name', 'Helpdesk Officer'))
            ->withCount([
                'assignedGrievances as active_assigned_grievances' => fn (Builder $grievances) => $grievances
                    ->whereNotIn('status', ['resolved', 'closed', 'rejected']),
            ]);

        if ($approverUserId !== null) {
            return $query->whereKey($approverUserId)->first();
        }

        $priorityClauses = [];
        $bindings = [];

        foreach ([
            'district_id' => 0,
            'section_id' => 1,
            'division_id' => 2,
        ] as $column => $priority) {
            $value = $grievance->getAttribute($column);

            if ($value !== null) {
                $priorityClauses[] = "WHEN {$column} = ? THEN {$priority}";
                $bindings[] = $value;
            }
        }

        $query->orderByRaw(
            'CASE '.implode(' ', $priorityClauses).'
                WHEN district_id IS NULL AND section_id IS NULL AND division_id IS NULL THEN 3
                ELSE 4
            END',
            $bindings,
        )
            ->orderBy('active_assigned_grievances')
            ->orderBy('users.id');

        return $query->first();
    }
}
