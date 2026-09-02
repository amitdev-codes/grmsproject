<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\StoreResolutionRequest;
use Modules\Grievance\Http\Requests\UpdateResolutionRequest;
use Modules\Grievance\Models\Resolution;
use Modules\Grievance\Services\ResolutionService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResolutionController extends Controller
{
    public function __construct(protected ResolutionService $resolutionService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::Resolutions/Index', [
            ...$this->resolutionService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::Resolutions/ResolutionForm', $this->resolutionService->forCreate());
    }

    public function edit(Resolution $resolution): Response
    {
        return Inertia::render('Grievance::Resolutions/ResolutionForm', $this->resolutionService->forEdit($resolution));
    }

    public function store(StoreResolutionRequest $request): RedirectResponse
    {
        $this->resolutionService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('resolutions.index')->with('success', 'Resolution created successfully.');
    }

    public function update(UpdateResolutionRequest $request, Resolution $resolution): RedirectResponse
    {
        $this->resolutionService->update(
            $resolution,
            $request->validated()
        );

        return redirect()->route('resolutions.index')->with('success', 'Resolution updated successfully.');
    }

    public function destroy(Resolution $resolution): RedirectResponse
    {
        $this->resolutionService->destroy($resolution);

        return back()->with('success', 'Resolution deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:resolution_categories,id',
        ]);

        $this->resolutionService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected Resolution deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->resolutionService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->resolutionService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} Resolution.")
            ->with('import_failures', $result['failures']);
    }
}
