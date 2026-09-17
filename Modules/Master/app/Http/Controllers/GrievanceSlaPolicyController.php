<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreGrievanceSlaPolicyRequest;
use Modules\Master\Http\Requests\UpdateGrievanceSlaPolicyRequest;
use Modules\Master\Models\GrievanceSlaPolicy;
use Modules\Master\Services\GrievanceSlaPolicyService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceSlaPolicyController extends Controller
{
    public function __construct(protected GrievanceSlaPolicyService $grievanceSlaPolicyService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::GrievanceSlaPolicy/Index', [
            ...$this->grievanceSlaPolicyService->table($request)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::GrievanceSlaPolicy/GrievanceSlaPolicyForm', $this->grievanceSlaPolicyService->forCreate());
    }

    public function edit(GrievanceSlaPolicy $grievanceSlaPolicy): Response
    {
        return Inertia::render('Master::GrievanceSlaPolicy/GrievanceSlaPolicyForm', $this->grievanceSlaPolicyService->forEdit($grievanceSlaPolicy));
    }

    public function store(StoreGrievanceSlaPolicyRequest $request): RedirectResponse
    {
        $this->grievanceSlaPolicyService->store($request->validated());

        return redirect()->route('grievance-sla-policies.index')->with('success', 'SLA policy created successfully.');
    }

    public function update(UpdateGrievanceSlaPolicyRequest $request, GrievanceSlaPolicy $grievanceSlaPolicy): RedirectResponse
    {
        $this->grievanceSlaPolicyService->update($grievanceSlaPolicy, $request->validated());

        return redirect()->route('grievance-sla-policies.index')->with('success', 'SLA policy updated successfully.');
    }

    public function destroy(GrievanceSlaPolicy $grievanceSlaPolicy): RedirectResponse
    {
        $this->grievanceSlaPolicyService->destroy($grievanceSlaPolicy);

        return back()->with('success', 'SLA policy deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_sla_policies,id',
        ]);

        $this->grievanceSlaPolicyService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected SLA policies deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceSlaPolicyService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceSlaPolicyService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} SLA policies.")
            ->with('import_failures', $result['failures']);
    }
}