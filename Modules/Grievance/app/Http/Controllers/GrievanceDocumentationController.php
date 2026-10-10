<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Grievance\Models\GrievanceWorkflowStep;
use Modules\Setting\Models\ApplicationSetting;
use Modules\Setting\Models\GrievanceIntakeSecuritySetting;
use Spatie\Permission\Models\Role;

class GrievanceDocumentationController extends Controller
{
    public function __invoke(): View
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'name' => $role->name,
                'active_users' => User::query()
                    ->where('status', true)
                    ->role($role->name)
                    ->count(),
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
            ]);

        $settings = ApplicationSetting::query()->first();
        $intakeSecurity = GrievanceIntakeSecuritySetting::current();

        return view('grievance::documentation', [
            'applicationName' => $settings?->project_name ?? config('app.name'),
            'applicationDescription' => $settings?->description,
            'generatedAt' => now(),
            'roles' => $roles,
            'workflowSteps' => GrievanceWorkflowStep::query()
                ->where('workflow_key', 'default')
                ->where('is_active', true)
                ->orderBy('step_number')
                ->get([
                    'step_number',
                    'name',
                    'description',
                    'role_name',
                    'approver_user_id',
                    'approval_action',
                    'rejection_action',
                    'rejection_target_step',
                    'is_final_approval',
                ]),
            'channels' => GrievanceChannel::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['code', 'name']),
            'categoryCount' => GrievanceCategory::active()->count(),
            'intakeSecurity' => [
                'requests_per_minute' => $intakeSecurity->lodging_requests_per_minute,
                'duplicate_window_hours' => $intakeSecurity->duplicate_window_hours,
                'captcha_provider' => $intakeSecurity->captcha_provider,
            ],
        ]);
    }
}
