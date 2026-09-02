<?php

namespace Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Http\Requests\StoreDistrictRequest;
use Modules\Master\Http\Requests\UpdateDistrictRequest;
use Modules\Master\Models\District;
use Modules\Master\Services\DistrictService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DistrictController extends Controller
{
    public function __construct(protected DistrictService $districtService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Master::District/Index', [
            ...$this->districtService->table($request)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Master::District/DistrictForm', $this->districtService->forCreate());
    }

    public function edit(District $district): Response
    {
        return Inertia::render('Master::District/DistrictForm', $this->districtService->forEdit($district));
    }

    public function store(StoreDistrictRequest $request): RedirectResponse
    {
        $this->districtService->store($request->validated());

        return redirect()->route('districts.index')->with('success', 'District created successfully.');
    }

    public function update(UpdateDistrictRequest $request, District $district): RedirectResponse
    {
        $this->districtService->update(
            $district,
            $request->validated()
        );

        return redirect()->route('districts.index')->with('success', 'District  updated successfully.');
    }

    public function destroy(District $district): RedirectResponse
    {
        $this->districtService->destroy($district);

        return back()->with('success', 'District deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:districts,id',
        ]);

        $this->districtService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected District deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->districtService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->districtService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} District.")
            ->with('import_failures', $result['failures']);
    }

}
