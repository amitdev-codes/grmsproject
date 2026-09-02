<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Grievance\Exports\GrievancesExport;
use Modules\Grievance\Http\Requests\BulkDeleteGrievanceRequest;
use Modules\Grievance\Http\Requests\StoreGrievanceRequest;
use Modules\Grievance\Http\Requests\UpdateGrievanceRequest;
use Modules\Grievance\Http\Resources\GrievanceResource;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Grievance\Models\InboundSms;
use Modules\Grievance\Services\GrievanceRegistrationService;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceController extends Controller
{
    public function __construct(protected GrievanceRegistrationService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'channel', 'status', 'category_id', 'district_id',
            'division_id', 'section_id', 'date_from', 'date_to', 'search',
        ]);

        $paginator = $this->service->paginate($filters);

        return Inertia::render('Grievance::Grievances/Index', [
            'data' => GrievanceResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from'         => $paginator->firstItem(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'to'           => $paginator->lastItem(),
                'total'        => $paginator->total(),
            ],
            'pendingSms' => InboundSms::where('status', 'pending')->latest()->limit(20)->get(),
            'categories' => GrievanceCategory::active()->get(['id', 'code', 'name_en']),
            'districts'  => District::orderBy('name')->get(['id', 'code', 'name']),
            'divisions'  => Division::orderBy('name')->get(['id', 'code', 'name']),
            'sections'   => Section::orderBy('name')->get(['id', 'code', 'name']),
            'filters'    => $filters,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Grievance::Grievances/GrievanceForm', [
            'channels' => GrievanceChannel::active()->get(['id', 'code', 'name']),
            'categories' => GrievanceCategory::active()->get(['id', 'code', 'name_en']),
            'districts' => District::orderBy('name')->get(['id', 'code', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'code', 'name']),
            'sections'   => Section::orderBy('name')->get(['id', 'code', 'name']),
            'fromInboundSms' => InboundSms::whereKey($request->input('inbound_sms_id'))->first(),
        ]);
    }

    public function store(StoreGrievanceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $grievance = $this->service->register(
            categoryId: $data['grievance_category_id'],
            districtId: $data['district_id'] ?? null,
            description: $data['description'],
            phone: $data['complainant_phone'] ?? null,
            channelCode: 'helpdesk',
            isAnonymous: $data['is_anonymous'] ?? false,
            email: $data['complainant_email'] ?? null,
            name: $data['complainant_name'] ?? null,
            registeredBy: $request->user()->id,
            attachments: $request->file('attachments', []),
        );

        if (! empty($data['inbound_sms_id'])) {
            InboundSms::whereKey($data['inbound_sms_id'])->update([
                'status' => 'registered', 'grievance_id' => $grievance->id, 'registered_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('grievances.index')->with('success', "Grievance registered: {$grievance->reference_no}");
    }

    public function edit(Grievance $grievance): Response
    {
        return Inertia::render('Grievance::Grievances/GrievanceForm', [
            'grievance' => new GrievanceResource($grievance->load('category', 'channel', 'district', 'media')),
            'categories' => GrievanceCategory::active()->get(['id', 'code', 'name_en']),
            'channels' => GrievanceChannel::active()->get(['id', 'code', 'name']),
            'districts' => District::orderBy('name')->get(['id', 'code', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'code', 'name']),
            'sections'   => Section::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function update(UpdateGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $data = $request->safe()->except(['attachments', 'remove_media_ids']);

        $this->service->update(
            $grievance,
            $data,
            $request->file('attachments', []),
            $request->input('remove_media_ids', []),
        );

        return redirect()->route('grievances.index')->with('success', 'Grievance updated.');
    }

    public function bulkDestroy(BulkDeleteGrievanceRequest $request): RedirectResponse
    {
        $count = $this->service->bulkDelete($request->validated('ids'));

        return back()->with('success', "{$count} grievance(s) deleted.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['channel', 'status', 'category_id', 'district_id', 'date_from', 'date_to', 'search']);

        return Excel::download(new GrievancesExport($this->service->exportQuery($filters)), 'grievances-'.now()->format('Ymd_His').'.xlsx');
    }
}
