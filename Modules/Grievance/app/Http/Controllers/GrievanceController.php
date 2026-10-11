<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        $listType = $request->query('list');
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['Admin', 'Super Admin']);
        $isDirectorResolvedList = $listType === 'resolved' && $user->hasRole('Director');
        $isAdminList = in_array($listType, ['pending', 'resolved', 'rejected'], true);

        if ($isAdminList && ! $isAdmin && ! $isDirectorResolvedList) {
            abort(403);
        }

        $filters = $request->only([
            'channel', 'status', 'category_id', 'district_id',
            'division_id', 'section_id', 'date_from', 'date_to', 'search',
        ]);
        $filters['terminal_assigned_to'] = $isAdminList ? null : $user->id;
        $listTitle = 'Grievances';

        if ($isAdminList) {
            if ($listType === 'pending') {
                $filters['exclude_statuses'] = ['resolved', 'closed', 'rejected'];
            } else {
                $filters['status'] = $listType === 'resolved' ? ['resolved', 'closed'] : 'rejected';

                if ($isDirectorResolvedList) {
                    $filters['resolution_approved_by'] = $user->id;
                }
            }
            $listTitle = ucfirst($listType).' Grievances';
        } elseif ($listType === 'forwarded') {
            if ($user->hasAnyRole(['Admin', 'Super Admin'])) {
                abort(403);
            }

            $filters['workflow_forwarded_by_user_id'] = $user->id;
            $listTitle = 'Forwarded Grievances';
        } elseif ($request->boolean('pending') || $request->routeIs('grievances.pending')) {

            if ($user->hasRole('Director')) {
                $filters['status'] = ['submitted', 'reallocation_required', 'pending_approval'];
            } elseif ($user->hasRole('Division Director') && $user->division_id) {
                $filters['status'] = 'allocated_division';
                $filters['division_id'] = $user->division_id;
            } elseif ($user->hasRole('Section Manager') && $user->section_id) {
                $filters['status'] = 'allocated_section';
                $filters['section_id'] = $user->section_id;
            } elseif ($user->hasAnyRole(['Helpdesk Officer', 'Content Editor'])) {
                $filters['status'] = ['assigned_officer', 'assigned', 'in_progress', 'escalated'];
                $filters['assigned_officer_id'] = $user->id;
            } else {
                abort(403);
            }
        }

        $paginator = $this->service->paginate($filters);
        $grievances = $paginator->getCollection()->load([
            'statusHistories',
            'currentWorkflowStep.approverUser',
            'assignedOfficer',
            'division',
            'section',
        ]);
        $this->attachWorkflowRecipients($grievances);

        return Inertia::render('Grievance::Grievances/Index', [
            'data' => GrievanceResource::collection($paginator->items()),
            'listType' => $listType,
            'listTitle' => $listTitle,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'pendingSms' => InboundSms::where('status', 'pending')->latest()->limit(20)->get(),
            'categories' => GrievanceCategory::active()->get(['id', 'code', 'name_en']),
            'districts' => District::orderBy('name')->get(['id', 'code', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'code', 'name']),
            'sections' => Section::orderBy('name')->get(['id', 'code', 'name']),
            'officers' => User::role('Helpdesk Officer')->orderBy('name')->get(['id', 'name', 'section_id']),
            'filters' => $filters,
        ]);
    }

    /**
     * Attach the users notified for each current workflow step without querying
     * once per grievance row.
     *
     * @param  Collection<int, Grievance>  $grievances
     */
    private function attachWorkflowRecipients(Collection $grievances): void
    {
        $roleNames = $grievances
            ->map(fn (Grievance $grievance) => $grievance->currentWorkflowStep?->role_name)
            ->filter()
            ->unique()
            ->values();

        $usersByRole = $roleNames->isEmpty()
            ? collect()
            : User::query()
                ->with('roles:id,name')
                ->whereHas('roles', fn ($query) => $query->whereIn('name', $roleNames))
                ->get(['id', 'name', 'division_id', 'section_id'])
                ->flatMap(fn (User $user) => $user->roles
                    ->whereIn('name', $roleNames)
                    ->map(fn ($role) => [$role->name, $user]))
                ->groupBy(fn (array $membership) => $membership[0])
                ->map(fn ($memberships) => $memberships->pluck(1)->values());

        foreach ($grievances as $grievance) {
            $step = $grievance->currentWorkflowStep;
            $recipients = collect();

            if ($step?->approverUser) {
                $recipients->push($step->approverUser);
            } elseif ($step?->action_type === 'prepare_resolution') {
                if ($grievance->assignedOfficer) {
                    $recipients->push($grievance->assignedOfficer);
                }
            } elseif ($step?->role_name) {
                $recipients = $usersByRole->get($step->role_name, collect())
                    ->filter(fn (User $user) => match ($step->action_type) {
                        'allocate_section' => $user->division_id === $grievance->division_id,
                        'assign_officer' => $user->section_id === $grievance->section_id,
                        default => true,
                    })
                    ->values();
            }

            $grievance->setAttribute(
                'workflow_pending_users',
                $recipients->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])->all(),
            );
        }
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Grievance::Grievances/GrievanceForm', [
            'channels' => GrievanceChannel::active()->get(['id', 'code', 'name']),
            'categories' => GrievanceCategory::active()->get(['id', 'code', 'name_en']),
            'districts' => District::orderBy('name')->get(['id', 'code', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'code', 'name']),
            'sections' => Section::orderBy('name')->get(['id', 'code', 'name']),
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

    public function edit(Request $request, Grievance $grievance): Response|RedirectResponse
    {
        abort_if($request->user()->hasAnyRole(['Admin', 'Super Admin']), 403);

        if (! Schema::hasTable('grievance_workflow_actions')
            || ! Schema::hasTable('grievance_workflow_documents')) {
            return redirect()->route('grievances.index')
                ->with('error', 'Grievance workflow database migrations are pending. Run php artisan migrate before opening this grievance.');
        }

        $grievance->load([
            'category',
            'channel',
            'district',
            'division',
            'section',
            'assignedOfficer',
            'media',
            'statusHistories',
            'currentWorkflowStep',
            'workflowActions.fromStep',
            'workflowActions.toStep',
            'workflowActions.actor',
            'workflowActions.documents',
            'resolutions' => fn ($query) => $query->latest('id')->limit(1),
        ]);

        return Inertia::render('Grievance::Grievances/GrievanceForm', [
            'grievance' => new GrievanceResource($grievance),
            'categories' => GrievanceCategory::active()->get(['id', 'code', 'name_en']),
            'channels' => GrievanceChannel::active()->get(['id', 'code', 'name']),
            'districts' => District::orderBy('name')->get(['id', 'code', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'code', 'name']),
            'sections' => Section::query()
                ->when($grievance->division_id, fn ($query) => $query->where('division_id', $grievance->division_id))
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'division_id']),
            'officers' => User::role('Helpdesk Officer')
                ->where('status', true)
                ->when($grievance->section_id, fn ($query) => $query->where(
                    fn ($officers) => $officers
                        ->where('section_id', $grievance->section_id)
                        ->orWhereNull('section_id'),
                ))
                ->orderBy('name')->get(['id', 'name']),
            'workflowStep' => $grievance->currentWorkflowStep,
            'workflowActions' => $grievance->workflowActions->map(fn ($action): array => [
                'id' => $action->id,
                'decision' => $action->decision,
                'reason' => $action->reason,
                'from_step' => $action->fromStep?->name,
                'to_step' => $action->toStep?->name,
                'actor' => $action->actor?->name,
                'created_at' => $action->created_at,
                'documents' => $action->documents->map(fn ($document): array => [
                    'id' => $document->id,
                    'original_name' => $document->original_name,
                    'mime_type' => $document->mime_type,
                    'download_url' => route('grievances.workflow-documents.download', $document),
                ])->values(),
            ])->values(),
            'resolutionDraft' => $grievance->resolutions->first(),
        ]);
    }

    public function update(UpdateGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        abort_if($request->user()->hasAnyRole(['Admin', 'Super Admin']), 403);

        $data = $request->safe()->except(['attachments', 'remove_attachment_ids']);
        if (array_key_exists('remarks', $data)) {
            $data['closed_reason'] = $data['remarks'];
            unset($data['remarks']);
        }

        $this->service->update(
            $grievance,
            $data,
            $request->file('attachments', []),
            $request->input('remove_attachment_ids', []),
        );

        return redirect()->route('grievances.index')->with('success', 'Grievance updated.');
    }

    public function destroy(Request $request, Grievance $grievance): RedirectResponse
    {
        abort_unless($this->canDelete($request, $grievance), 403);

        $grievance->delete();

        return back()->with('success', 'Grievance deleted.');
    }

    public function bulkDestroy(BulkDeleteGrievanceRequest $request): RedirectResponse
    {
        $count = $this->service->bulkDelete($request->validated('ids'));

        return back()->with('success', "{$count} grievance(s) deleted.");
    }

    private function canDelete(Request $request, Grievance $grievance): bool
    {
        $user = $request->user();

        return $user !== null
            && $user->can('grievances.delete')
            && ($user->hasAnyRole(['Admin', 'Super Admin']) || $grievance->registered_by === $user->id);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['channel', 'status', 'category_id', 'district_id', 'date_from', 'date_to', 'search']);

        return Excel::download(new GrievancesExport($this->service->exportQuery($filters)), 'grievances-'.now()->format('Ymd_His').'.xlsx');
    }
}
