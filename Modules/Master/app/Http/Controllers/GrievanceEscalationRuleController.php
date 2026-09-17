<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreGrievanceEscalationRuleRequest;
use Modules\Master\Http\Requests\UpdateGrievanceEscalationRuleRequest;
use Modules\Master\Models\GrievanceEscalationRule;
use Modules\Master\Models\GrievanceSlaPolicy;
use Modules\Master\Services\GrievanceEscalationRuleService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceEscalationRuleController extends Controller
{
    public function __construct(protected GrievanceEscalationRuleService $grievanceEscalationRuleService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::GrievanceEscalationRule/Index', [
            ...$this->grievanceEscalationRuleService->table($request)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::GrievanceEscalationRule/GrievanceEscalationRuleForm', [
            ...$this->grievanceEscalationRuleService->forCreate(),
            'slaPolicies' => GrievanceSlaPolicy::active()->get(['id', 'name']),
        ]);
    }

    public function edit(GrievanceEscalationRule $grievanceEscalationRule): Response
    {
        return Inertia::render('Master::GrievanceEscalationRule/GrievanceEscalationRuleForm', [
            ...$this->grievanceEscalationRuleService->forEdit($grievanceEscalationRule),
            'slaPolicies' => GrievanceSlaPolicy::active()->get(['id', 'name']),
        ]);
    }

    public function store(StoreGrievanceEscalationRuleRequest $request): RedirectResponse
    {
        $this->grievanceEscalationRuleService->store($request->validated());

        return redirect()->route('grievance-escalation-rules.index')->with('success', 'Escalation rule created successfully.');
    }

    public function update(UpdateGrievanceEscalationRuleRequest $request, GrievanceEscalationRule $grievanceEscalationRule): RedirectResponse
    {
        $this->grievanceEscalationRuleService->update($grievanceEscalationRule, $request->validated());

        return redirect()->route('grievance-escalation-rules.index')->with('success', 'Escalation rule updated successfully.');
    }

    public function destroy(GrievanceEscalationRule $grievanceEscalationRule): RedirectResponse
    {
        $this->grievanceEscalationRuleService->destroy($grievanceEscalationRule);

        return back()->with('success', 'Escalation rule deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_escalation_rules,id',
        ]);

        $this->grievanceEscalationRuleService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected escalation rules deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceEscalationRuleService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceEscalationRuleService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} escalation rules.")
            ->with('import_failures', $result['failures']);
    }
}