<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Grievance\Datatable\GrievanceEscalationDataTable;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceEscalation;
use Modules\Grievance\Repositories\GrievanceEscalationRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceEscalationService
{
    public function __construct(
        protected GrievanceEscalationRepository $repository,
        protected GrievanceEscalationDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return [
            'grievanceEscalation' => null,
            ...$this->lookups(),
        ];
    }

    public function forEdit(GrievanceEscalation $grievanceEscalation): array
    {
        return [
            'grievanceEscalation' => $grievanceEscalation->load(['grievance', 'escalatedOfficer']),
            ...$this->lookups(),
        ];
    }

    /**
     * Dropdown/lookup data shared by the create and edit forms.
     */
    protected function lookups(): array
    {
        return [
            'grievances' => $this->grievanceOptions(),
            'officers' => $this->officerOptions(),
        ];
    }

    protected function grievanceOptions(): Collection
    {
        return Grievance::query()
            ->select(['id', 'reference_number'])
            ->orderBy('reference_number')
            ->get();
    }

    protected function officerOptions(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    // GrievanceEscalationService
    public function store(array $data): GrievanceEscalation
    {
        $data['sla_breached_at'] = $this->slaBreachedAt((int) $data['escalation_level']);

        return $this->repository->create($data);
    }

    protected function slaBreachedAt(int $level): CarbonInterface
    {
        return match ($level) {
            1 => now()->addHours(48),
            2 => now()->addDays(5),
            3 => now()->addDays(7),
            default => now(),
        };
    }

    public function update(GrievanceEscalation $grievanceEscalation, array $data): GrievanceEscalation
    {
        $this->repository->update($grievanceEscalation, $data);

        return $grievanceEscalation->refresh();
    }

    public function destroy(GrievanceEscalation $grievanceEscalation): bool
    {
        return $this->repository->delete($grievanceEscalation);
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
            'grievance-escalations-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }
}
