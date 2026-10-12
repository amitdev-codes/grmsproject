<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Grievance\Datatable\ResolutionDataTable;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\Resolution;
use Modules\Grievance\Repositories\ResolutionRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResolutionService
{
    public function __construct(
        protected ResolutionRepository $repository,
        protected ResolutionDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return [
            'resolution' => null,
            ...$this->lookups(),
        ];
    }

    public function forEdit(Resolution $resolution): array
    {
        return [
            'resolution' => $resolution->load(['grievance', 'user']),
            ...$this->lookups(),
        ];
    }

    protected function lookups(): array
    {
        return [
            'grievances' => $this->grievanceOptions(),
            'users' => $this->userOptions(),
        ];
    }

    protected function grievanceOptions(): Collection
    {
        return Grievance::query()
            ->whereIn('status', ['in_progress', 'escalated'])
            ->where('assigned_officer_id', Auth::id())
            ->whereHas('currentWorkflowStep', fn ($query) => $query
                ->where('is_active', true)
                ->whereIn('action_type', ['prepare_resolution', 'investigation']))
            ->select(['id', 'reference_no as reference_number'])
            ->orderBy('reference_no')
            ->get();
    }

    protected function userOptions(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    public function store(array $data): Resolution
    {
        $data['resolution_text'] = app(ResolutionDraftSanitizer::class)->sanitize($data['resolution_text']);

        if (trim(strip_tags($data['resolution_text'])) === '') {
            throw ValidationException::withMessages(['resolution_text' => 'Enter resolution details before saving the draft.']);
        }

        $data['proposed_by'] = Auth::id();

        return $this->repository->create($data);
    }

    public function update(Resolution $resolution, array $data): Resolution
    {
        $this->repository->update($resolution, $data);

        return $resolution->refresh();
    }

    public function destroy(Resolution $resolution): bool
    {
        return $this->repository->delete($resolution);
    }

    public function bulkDestroy(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->repository->export(
            $this->dataTable->exportColumns(),
            $this->dataTable->exportQuery($request),
            'grievance-messages-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }
}
