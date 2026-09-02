<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\StoreGrievanceMessageRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceMessageRequest;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Services\GrievanceMessageService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceMessageController extends Controller
{
    public function __construct(protected GrievanceMessageService $grievanceMessageService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::GrievancesMessages/Index', [
            ...$this->grievanceMessageService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::GrievancesMessages/GrievanceMessageForm', $this->grievanceMessageService->forCreate());
    }

    public function edit(GrievanceMessage $grievanceMessage): Response
    {
        return Inertia::render('Grievance::GrievancesMessages/GrievanceMessageForm', $this->grievanceMessageService->forEdit($grievanceMessage));
    }

    public function store(StoreGrievanceMessageRequest $request): RedirectResponse
    {
        $this->grievanceMessageService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('grievance-messages.index')->with('success', 'Grievance Messages created successfully.');
    }

    public function update(UpdateGrievanceMessageRequest $request, GrievanceMessage $grievanceMessage): RedirectResponse
    {
        $this->grievanceMessageService->update(
            $grievanceMessage,
            $request->validated()
        );

        return redirect()->route('grievance-messages.index')->with('success', 'Grievance Messages updated successfully.');
    }

    public function destroy(GrievanceMessage $grievanceMessage): RedirectResponse
    {
        $this->grievanceMessageService->destroy($grievanceMessage);

        return back()->with('success', 'GrievanceMessage deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_categories,id',
        ]);

        $this->grievanceMessageService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected GrievanceMessage deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceMessageService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceMessageService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} GrievanceMessage.")
            ->with('import_failures', $result['failures']);
    }
}
