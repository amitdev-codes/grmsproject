<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreProjectTypeRequest;
use Modules\Master\Http\Requests\UpdateProjectTypeRequest;
use Modules\Master\Models\ProjectType;
use Modules\Master\Services\ProjectTypeService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectTypeController extends Controller
{
    public function __construct(protected ProjectTypeService $projectTypeService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::ProjectType/Index', [
            ...$this->projectTypeService->table($request)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::ProjectType/ProjectTypeForm', $this->projectTypeService->forCreate());
    }

    public function edit(ProjectType $projectType): Response
    {
        return Inertia::render('Master::ProjectType/ProjectTypeForm', $this->projectTypeService->forEdit($projectType));
    }

    public function store(StoreProjectTypeRequest $request): RedirectResponse
    {
        $this->projectTypeService->store($request->validated());

        return redirect()->route('project-types.index')->with('success', 'Project type created successfully.');
    }

    public function update(UpdateProjectTypeRequest $request, ProjectType $projectType): RedirectResponse
    {
        $this->projectTypeService->update($projectType, $request->validated());

        return redirect()->route('project-types.index')->with('success', 'Project type updated successfully.');
    }

    public function destroy(ProjectType $projectType): RedirectResponse
    {
        $this->projectTypeService->destroy($projectType);

        return back()->with('success', 'Project type deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:project_types,id',
        ]);

        $this->projectTypeService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected project types deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->projectTypeService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->projectTypeService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} project types.")
            ->with('import_failures', $result['failures']);
    }
}