<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreSectionRequest;
use Modules\Master\Http\Requests\UpdateSectionRequest;
use Modules\Master\Models\Section;
use Modules\Master\Services\SectionService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SectionController extends Controller
{
    public function __construct(protected SectionService $sectionService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::Section/Index', [
            ...$this->sectionService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::Section/SectionForm', $this->sectionService->forCreate());
    }

    public function edit(Section $section): Response
    {
        return Inertia::render('Master::Section/SectionForm', $this->sectionService->forEdit($section));
    }

    public function store(StoreSectionRequest $request): RedirectResponse
    {
        $this->sectionService->store($request->validated());

        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $this->sectionService->update(
            $section,
            $request->validated()
        );

        return redirect()->route('sections.index')->with('success', 'Section  updated successfully.');
    }

    public function destroy(Section $Section): RedirectResponse
    {
        $this->sectionService->destroy($Section);

        return back()->with('success', 'Section deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:Sections,id',
        ]);

        $this->sectionService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected Section deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->sectionService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->sectionService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} Section.")
            ->with('import_failures', $result['failures']);
    }
}
