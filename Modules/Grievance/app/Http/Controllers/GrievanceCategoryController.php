<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\StoreGrievanceCategoryRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceCategoryRequest;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Services\GrievanceCategoryService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceCategoryController extends Controller
{
    public function __construct(protected GrievanceCategoryService $grievanceCategoryService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::GrievanceCategories/Index', [
            ...$this->grievanceCategoryService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::GrievanceCategories/GrievanceCategoryForm', $this->grievanceCategoryService->forCreate());
    }

    public function edit(GrievanceCategory $grievanceCategory): Response
    {
        return Inertia::render('Grievance::GrievanceCategories/GrievanceCategoryForm', $this->grievanceCategoryService->forEdit($grievanceCategory));
    }

    public function store(StoreGrievanceCategoryRequest $request): RedirectResponse
    {
        $this->grievanceCategoryService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('grievance-categories.index')->with('success', 'Grievance Category created successfully.');
    }

    public function update(UpdateGrievanceCategoryRequest $request, GrievanceCategory $grievanceCategory): RedirectResponse
    {
        $this->grievanceCategoryService->update(
            $grievanceCategory,
            $request->validated()
        );

        return redirect()->route('grievance-categories.index')->with('success', 'Grievance Category updated successfully.');
    }

    public function destroy(GrievanceCategory $grievanceCategory): RedirectResponse
    {
        $this->grievanceCategoryService->destroy($grievanceCategory);

        return back()->with('success', 'GrievanceCategory deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_categories,id',
        ]);

        $this->grievanceCategoryService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected GrievanceCategory deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceCategoryService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceCategoryService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} GrievanceCategory.")
            ->with('import_failures', $result['failures']);
    }
}
