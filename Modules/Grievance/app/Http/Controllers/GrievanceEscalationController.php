<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\StoreGrievanceEscalationRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceEscalationRequest;
use Modules\Grievance\Models\GrievanceEscalation;
use Modules\Grievance\Services\GrievanceEscalationService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceEscalationController extends Controller
{
    public function __construct(protected GrievanceEscalationService $grievanceEscalationService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::GrievanceEscalations/Index', [
            ...$this->grievanceEscalationService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::GrievanceEscalations/GrievanceEscalationForm', $this->grievanceEscalationService->forCreate());
    }

    public function edit(GrievanceEscalation $grievanceEscalation): Response
    {
        return Inertia::render('Grievance::GrievanceEscalations/GrievanceEscalationForm', $this->grievanceEscalationService->forEdit($grievanceEscalation));
    }

    public function store(StoreGrievanceEscalationRequest $request): RedirectResponse
    {
        $this->grievanceEscalationService->store($request->validated());

        return redirect()->route('grievance-escalations.index')->with('success', 'Escalation created successfully.');
    }

    public function update(UpdateGrievanceEscalationRequest $request, GrievanceEscalation $grievanceEscalation): RedirectResponse
    {
        $this->grievanceEscalationService->update(
            $grievanceEscalation,
            $request->validated()
        );

        return redirect()->route('grievance-escalations.index')->with('success', 'User updated successfully.');
    }

    public function destroy(GrievanceEscalation $grievanceEscalation): RedirectResponse
    {
        $this->grievanceEscalationService->destroy($grievanceEscalation);

        return back()->with('success', 'GrievanceEscalation deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_escalations,id',
        ]);

        $this->grievanceEscalationService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected escalations deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceEscalationService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceEscalationService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} GrievanceEscalation.")
            ->with('import_failures', $result['failures']);
    }
}
