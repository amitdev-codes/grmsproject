<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreProjectRequest;
use Modules\Master\Http\Requests\UpdateProjectRequest;
use Modules\Master\Models\Project;
use Modules\Master\Services\ProjectService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::Project/Index', [
            ...$this->projectService->table($request)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::Project/ProjectForm', $this->projectService->forCreate());
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Master::Project/ProjectForm', $this->projectService->forEdit($project));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->projectService->store($request->validated());

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projectService->destroy($project);

        return back()->with('success', 'Project deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:projects,id',
        ]);

        $this->projectService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected projects deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->projectService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->projectService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} projects.")
            ->with('import_failures', $result['failures']);
    }
}