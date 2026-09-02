<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\StoreGrievanceCategoryRequest;
use Modules\Grievance\Http\Requests\StoreGrievanceChannelRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceCategoryRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceChannelRequest;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Grievance\Services\GrievanceChannelService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceChannelController extends Controller
{
    public function __construct(protected GrievanceChannelService $grievanceChannelService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Grievance::GrievanceChannels/Index', [
            ...$this->grievanceChannelService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grievance::GrievanceChannels/GrievanceChannelForm', $this->grievanceChannelService->forCreate());
    }

    public function edit(GrievanceChannel $grievanceChannel): Response
    {
        return Inertia::render('Grievance::GrievanceChannels/GrievanceChannelForm', $this->grievanceChannelService->forEdit($grievanceChannel));
    }

    public function store(StoreGrievanceChannelRequest $request): RedirectResponse
    {
        $this->grievanceChannelService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('grievance-channels.index')->with('success', 'Grievance Channel created successfully.');
    }

    public function update(UpdateGrievanceChannelRequest $request, GrievanceChannel $grievanceChannel): RedirectResponse
    {
        $this->grievanceChannelService->update(
            $grievanceChannel,
            $request->validated()
        );

        return redirect()->route('grievance-channels.index')->with('success', 'Grievance Channel updated successfully.');
    }

    public function destroy(GrievanceChannel $grievanceChannel): RedirectResponse
    {
        $this->grievanceChannelService->destroy($grievanceChannel);

        return back()->with('success', 'Grievance Channel deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:grievance_channels,id',
        ]);

        $this->grievanceChannelService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected Grievance Channel deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->grievanceChannelService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->grievanceChannelService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} Grievance Channel.")
            ->with('import_failures', $result['failures']);
    }
}
