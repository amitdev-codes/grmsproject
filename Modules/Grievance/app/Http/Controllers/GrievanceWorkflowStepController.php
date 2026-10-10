<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Datatable\GrievanceWorkflowStepDataTable;
use Modules\Grievance\Http\Requests\StoreGrievanceWorkflowStepRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceWorkflowStepRequest;
use Modules\Grievance\Models\GrievanceWorkflowStep;
use Spatie\Permission\Models\Role;

class GrievanceWorkflowStepController extends Controller
{
    public function __construct(protected GrievanceWorkflowStepDataTable $dataTable) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::GrievanceWorkflowSteps/Index', [
            ...$this->dataTable->toArray($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::GrievanceWorkflowSteps/GrievanceWorkflowStepForm', [
            'workflowStep' => null,
            ...$this->formOptions(),
        ]);
    }

    public function edit(GrievanceWorkflowStep $grievanceWorkflowStep): Response
    {
        abort_unless($grievanceWorkflowStep->workflow_key === 'default', 404);

        return Inertia::render('Grievance::GrievanceWorkflowSteps/GrievanceWorkflowStepForm', [
            'workflowStep' => $grievanceWorkflowStep,
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreGrievanceWorkflowStepRequest $request): RedirectResponse
    {
        GrievanceWorkflowStep::create([
            ...$request->validated(),
            'workflow_key' => 'default',
        ]);

        return redirect()->route('grievance-workflow-steps.index')->with('success', 'Workflow step created successfully.');
    }

    public function update(UpdateGrievanceWorkflowStepRequest $request, GrievanceWorkflowStep $grievanceWorkflowStep): RedirectResponse
    {
        abort_unless($grievanceWorkflowStep->workflow_key === 'default', 404);
        $grievanceWorkflowStep->update($request->validated());

        return redirect()->route('grievance-workflow-steps.index')->with('success', 'Workflow step updated successfully.');
    }

    public function destroy(GrievanceWorkflowStep $grievanceWorkflowStep): RedirectResponse
    {
        abort_unless($grievanceWorkflowStep->workflow_key === 'default', 404);

        $isReturnTarget = GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('rejection_action', 'return_to_step')
            ->where('rejection_target_step', $grievanceWorkflowStep->step_number)
            ->exists();

        if ($isReturnTarget) {
            return back()->withErrors([
                'workflowStep' => 'This level is a rejection destination. Update the steps that return to it before deleting it.',
            ]);
        }

        $grievanceWorkflowStep->delete();

        return back()->with('success', 'Workflow step deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(['name'])->map(
                fn (Role $role) => ['value' => $role->name, 'label' => $role->name],
            )->values(),
            'users' => \App\Models\User::query()->orderBy('name')->get(['id', 'name'])->map(
                fn ($user) => ['value' => (string) $user->id, 'label' => $user->name],
            )->values(),
        ];
    }
}
