<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreDivisionRequest;
use Modules\Master\Http\Requests\UpdateDivisionRequest;
use Modules\Master\Models\Division;
use Modules\Master\Services\DivisionService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DivisionController extends Controller
{
    public function __construct(protected DivisionService $divisionService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::Division/Index', [
            ...$this->divisionService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::Division/DivisionForm', $this->divisionService->forCreate());
    }

    public function edit(Division $division): Response
    {
        return Inertia::render('Master::Division/DivisionForm', $this->divisionService->forEdit($division));
    }

    public function store(StoreDivisionRequest $request): RedirectResponse
    {
        $this->divisionService->store($request->validated());

        return redirect()->route('divisions.index')->with('success', 'Division created successfully.');
    }

    public function update(UpdateDivisionRequest $request, Division $division): RedirectResponse
    {
        $this->divisionService->update(
            $division,
            $request->validated()
        );

        return redirect()->route('divisions.index')->with('success', 'Division  updated successfully.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        $this->divisionService->destroy($division);

        return back()->with('success', 'Division deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:Divisions,id',
        ]);

        $this->divisionService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected Division deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->divisionService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->divisionService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} Division.")
            ->with('import_failures', $result['failures']);
    }
}
