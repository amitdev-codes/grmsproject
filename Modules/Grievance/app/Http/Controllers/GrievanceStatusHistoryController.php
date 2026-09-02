<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\StoreGrievanceStatusHistoryRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceStatusHistoryRequest;
use Modules\Grievance\Models\GrievanceStatusHistory;
use Modules\Grievance\Services\GrievanceStatusHistoryService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceStatusHistoryController extends Controller
{
    public function __construct(protected GrievanceStatusHistoryService $grievanceStatusHistoryService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::GrievancesStatusHistory/Index', [
            ...$this->grievanceStatusHistoryService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::GrievancesStatusHistory/GrievanceStatusHistoryForm', $this->grievanceStatusHistoryService->forCreate());
    }

    public function edit(GrievanceStatusHistory $grievanceStatusHistory): Response
    {
        return Inertia::render('Grievance::GrievancesStatusHistory/GrievanceStatusHistoryForm', $this->grievanceStatusHistoryService->forEdit($grievanceStatusHistory));
    }

    public function store(StoreGrievanceStatusHistoryRequest $request): RedirectResponse
    {
        $this->grievanceStatusHistoryService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('grievance-status-histories.index')->with('success', 'Grievance StatusHistory created successfully.');
    }

    public function update(UpdateGrievanceStatusHistoryRequest $request, GrievanceStatusHistory $grievanceStatusHistory): RedirectResponse
    {
        $this->grievanceStatusHistoryService->update(
            $grievanceStatusHistory,
            $request->validated()
        );

        return redirect()->route('grievance-status-histories.index')->with('success', 'Grievance StatusHistory updated successfully.');
    }

    public function destroy(GrievanceStatusHistory $grievanceStatusHistory): RedirectResponse
    {
        $this->grievanceStatusHistoryService->destroy($grievanceStatusHistory);

        return back()->with('success', 'GrievanceStatusHistory deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_categories,id',
        ]);

        $this->grievanceStatusHistoryService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected GrievanceStatusHistory deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceStatusHistoryService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceStatusHistoryService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} GrievanceStatusHistory.")
            ->with('import_failures', $result['failures']);
    }
}
